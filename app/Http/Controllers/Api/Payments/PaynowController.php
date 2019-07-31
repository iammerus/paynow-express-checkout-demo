<?php

namespace App\Http\Controllers\Api\Payments;

use App\Http\Controllers\Controller;
use App\Transaction;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Paynow\Http\ConnectionException;
use Paynow\Payments\HashMismatchException;
use Paynow\Payments\InvalidIntegrationException;
use Paynow\Payments\NotImplementedException;
use Paynow\Payments\Paynow;

class PaynowController extends Controller
{
    /**
     * @var Paynow|null
     */
    protected $paynow = null;

    public function __construct()
    {
        // TODO: Maybe bind an instance of this class on Laravel's IoC container and use dependency injection?
        $this->paynow = new Paynow(
            config('paynow.INTEGRATION_ID'),
            config('paynow.INTEGRATION_KEY'),
            url('/'),
            url('/')
        );
    }

    /**
     * Initialize  an express checkout transaction
     *
     * @param Request $request
     *
     * @return Response|string|JsonResponse
     * @throws NotImplementedException
     */
    public function initExpress(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required',
            'amount' => 'required',
            'email' => 'email'
        ]);

        // Get the payer's email address
        $email = $request->has('email') ? $request->input('email') : 'devnull@mailinator.com';

        // Get the payer's phone number
        $phone = $request->input('phone');

        // Get the amount of the transaction
        $amount = floatval($request->input('amount'));

        // Create a transaction instance
        $transaction = Transaction::create($request->except(['_token', 'phone', 'email']));

        // Set the user's phone as the transaction's instrument
        $transaction->instrument = $phone;
        $transaction->save();

        // Check if transaction was created without any errors
        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not initiate transaction'
            ]);
        }

        // Create a new payment passing in the transaction's ID as the payment's unique reference
        $payment = $this->paynow->createPayment($transaction->id, $email);

        $payment->add('Shopping Cart - ' . config('app.name'), $amount);

        try {
            // Send the transaction to Paynow
            $response = $this->paynow->sendMobile($payment, $phone, 'ecocash');

            // Check if our initiation failed
            if (!$response->success()) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Failed to initiate the transaction with Paynow",
                    'reason' => @$response->data()['error']
                ]);
            }

            //  Attach poll url to the transaction
            $transaction->poll_url = $response->pollUrl();
            $transaction->save();

            // Return the response
            return response()->json([
                'status' => 'success',
                'message' => $response->instructions(),
                'transaction' => $transaction
            ]);
        } catch (ConnectionException $e) {
            logger()->error("Failed to connect to Paynow\n" . $e->getTraceAsString());
        } catch (HashMismatchException $e) {
            logger()->error("Paynow hash validation failed\n" . $e->getTraceAsString());
        } catch (InvalidIntegrationException $e) {
            logger()->error("Paynow failed to validate our integration details\n" . $e->getTraceAsString());
        } catch (Exception $e) {
            logger()->error("General error thrown while sending transaction {$e->getMessage()}\n" . $e->getTraceAsString());
        }

        return response()->json([
            'status' => 'error',
            'message' => "Paynow encountered an error processing transaction"
        ]);
    }


    /**
     * Poll the status of the specified transaction
     *
     * @param Request $request
     *
     * @return Response|string|JsonResponse
     */
    public function pollTransaction(Request $request)
    {
        $this->validate($request, [
            'transaction' => 'exists:transactions,id'
        ]);

        // Find a transaction matching the given transaction id
        $transaction = Transaction::findOrFail($request->input('transaction'));

        try {
            // Try to poll the transaction
            $status = $this->paynow->pollTransaction($transaction->poll_url);

            // Return transaction status
            return response()->json([
                'status' => $status->paid() ? 'Paid' : 'Awaiting payment'
            ]);
        } catch (Exception $e) {
            // Log out the error
            logger()->error($e->getMessage() . "\t\t" . $e->getTraceAsString());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while polling transaction'
            ]);
        }
    }

    /**
     * When a transaction is paid for, run the actions specified on the Transaction record
     *
     * @param Transaction $transaction
     */
    protected function runPaidTransactionActions(Transaction $transaction)
    {
        // Some sanity checking
        if (!$transaction->paid) return;

        // Get the model for the action
        $model = $transaction->payment_model;

        if (is_null($model)) {
            return;
        }

        // Find the corresponding record
        $record = $model::find($transaction->payment_record);

        // Store payment column in shorter variable, laziness!
        $column = $transaction->payment_column;

        // Get the value from
        $value = $transaction->value ? $transaction->value : $transaction->amount;

        $newValue = null;

        // Compute new value using the defined action
        switch ($transaction->payment_action) {
            case 'add':
                $newValue = floatval($value) + floatval($record->{$column});
                break;

            case 'subtract':
                $newValue = floatval($value) - floatval($record->{$column});
                break;

            case 'value':
                $newValue = $value;
                break;
        }

        // Update the column on the model
        $record->{$transaction->payment_column} = $newValue;

        // Persist the record
        $record->save();
    }

    /**
     * This method handles status updates sent from Paynow
     *
     * @return void
     */
    public function statusUpdate()
    {
        try {
            $status = $this->paynow->processStatusUpdate();

            if ($status->paid()) {
                // Find the transaction which has been paid for
                $transaction = Transaction::find($status->reference());

                // Update the transaction of the transaction
                $transaction->paid = true;

                // Persist the new status
                $transaction->save();

                $this->runPaidTransactionActions($transaction);
            }
        } catch (HashMismatchException $e) {
            logger()->error("Hash mismatch exception when trying to process status update");
        }
    }
}
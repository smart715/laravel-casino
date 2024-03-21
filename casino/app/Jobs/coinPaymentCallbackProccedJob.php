<?php

namespace VanguardLTE\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use VanguardLTE\Payment;
use VanguardLTE\User;
use VanguardLTE\Transaction;

class coinPaymentCallbackProccedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data) {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        // Do something...
				
		if( isset($this->data['payload']['payment_id']) ){
			
			$payment = Transaction::find($this->data['payload']['payment_id']);
			
			if( $payment ){
				
				$user = User::find($payment->user_id);
				
				if( $user ){
					
					if( $this->data['request_type'] == 'schedule_transaction' ){
						$status = $this->data['status'];
					}
					
					if( $this->data['request_type'] == 'create_transaction' ){
						$status = $this->data['transaction']['status'];
					}
					
					
					if( $status == 100 ){			
						$payment->update(['status' => 1]);			
						$user->update(['balance' => $user->balance + $payment->summ, 
									'count_balance' => $user->count_balance + $payment->summ,
									'refunds' => $user->refunds + $payment->summ ]);

					}
					
					if( $status == -1 ){			
						$payment->update(['status' => -1]);					
					}
					
				}
				
			}
		}
			
					

        /* === Output data $request from task schedule === */
        // $this->data['request_type'] = 'schedule_transaction';
        // $this->data['payload']; // <--- Your payload data
        // $this->data['time_created'];
        // $this->data['time_expires'];
        // $this->data['status'];
        /*  -- Status transaction --
            0   : Waiting for buyer funds
            1   : Funds received and confirmed, sending to you shortly
            100 : Complete,
            -1  : Cancelled / Timed Out
        */
        // $this->data['status_text'];
        // $this->data['type'];
        // $this->data['coin'];
        // $this->data['amount'];
        // $this->data['amountf'];
        // $this->data['received'];
        // $this->data['receivedf'];
        // $this->data['recv_confirms'];
        // $this->data['payment_address'];
        // $this->data['time_completed']; // showing if "$this->data['status" is 100
    /* === End data $request from task schedule === */

    /* === Output data $request from Create Transaction === */
        // $this->data['request_type'] = 'create_transaction';
        // $this->data['params']; // <--- Your custom params
        // $this->data['payload']; // <--- Your payload data
        // $this->data['transaction']['time_created'];
        // $this->data['transaction']['time_expires'];
        // $this->data['transaction']['status'];
        // $this->data['transaction']['status_text'];
        // $this->data['transaction']['type'];
        // $this->data['transaction']['coin'];
        // $this->data['transaction']['amount'];
        // $this->data['transaction']['amountf'];
        // $this->data['transaction']['received'];
        // $this->data['transaction']['receivedf'];
        // $this->data['transaction']['recv_confirms'];
        // $this->data['transaction']['payment_address'];
    /* === End data $request from Create Transaction === */

    }
}

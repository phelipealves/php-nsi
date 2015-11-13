<?php

namespace NSI\web;

use NSI\CallbackReceiver;

class CallbackController {
    private $callbackReceiverListener;

    function __construct(CallbackReceiver $callbackReceiverListener) {
        if($callbackReceiverListener == null) {
            throw new \Exception("Callback receiver listener must be informed");
        }
        $this->callbackReceiverListener = $callbackReceiverListener;
    }

    public function dataPlaneStateChange($response) {
        $this->callbackReceiverListener->notifyDataPlaneStateChange($response);
    }

    public function messageDeliveryTimeout($response) {
        $this->callbackReceiverListener->notifyMessageDeliveryTimeout($response);
    }

    public function reserveConfirmed($response){
        $this->callbackReceiverListener->notifyReserveConfirmed($response);
    }

    public function reserveFailed($response) {
        $this->callbackReceiverListener->notifyReserveFailed($response);
    }

    public function reserveAbortConfirmed($response) {
        $this->callbackReceiverListener->notifyReserveAbortConfirmed($response);
    }

    public function reserveCommitConfirmed($response) {
        $this->callbackReceiverListener->notifyReserveCommitConfirmed($response);
    }

    public function reserveCommitFailed($response) {
        $this->callbackReceiverListener->notifyReserveCommitFailed($response);
    }

    public function provisionConfirmed($response) {
        $this->callbackReceiverListener->notifyProvisionConfirmed($response);
    }

    public function terminateConfirmed($response) {
        $this->callbackReceiverListener->notifyTerminateConfirmed($response);
    }

    public function releaseConfirmed($response) {
        $this->callbackReceiverListener->notifyReleaseConfirmed($response);
    }

    public function querySummaryConfirmed($response) {
        $this->callbackReceiverListener->notifyQuerySummaryConfirmed($response);
    }

    public function queryRecursiveConfirmed($response) {
        $this->callbackReceiverListener->notifyQueryRecursiveConfirmed($response);
    }

    public function queryNotificationConfirmed($response) {
        $this->callbackReceiverListener->notifyQueryNotificationConfirmed($response);
    }

    public function queryResultConfirmed($response) {
        $this->callbackReceiverListener->notifyQueryResultConfirmed($response);
    }

    public function error($response) {
        $this->callbackReceiverListener->notifyError($response);
    }

    public function errorEvent($response) {
        $this->callbackReceiverListener->notifyErrorEvent($response);
    }

    public function reserveTimeout($response) {
        $this->callbackReceiverListener->notifyReserveTimeout($response);
    }
}
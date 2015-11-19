<?php

namespace NSI\Web;

use NSI\CallbackReceiver;

class CallbackController
{
    private $listener;

    public function __construct(CallbackReceiver $listener)
    {
        if ($listener == null) {
            throw new \Exception('Callback receiver listener must be informed');
        }
        $this->listener = $listener;
    }

    public function dataPlaneStateChange($response)
    {
        $this->listener->notifyDataPlaneStateChange($response);
    }

    public function messageDeliveryTimeout($response)
    {
        $this->listener->notifyMessageDeliveryTimeout($response);
    }

    public function reserveConfirmed($response)
    {
        $this->listener->notifyReserveConfirmed($response);
    }

    public function reserveFailed($response)
    {
        $this->listener->notifyReserveFailed($response);
    }

    public function reserveAbortConfirmed($response)
    {
        $this->listener->notifyReserveAbortConfirmed($response);
    }

    public function reserveCommitConfirmed($response)
    {
        $this->listener->notifyReserveCommitConfirmed($response);
    }

    public function reserveCommitFailed($response)
    {
        $this->listener->notifyReserveCommitFailed($response);
    }

    public function provisionConfirmed($response)
    {
        $this->listener->notifyProvisionConfirmed($response);
    }

    public function terminateConfirmed($response)
    {
        $this->listener->notifyTerminateConfirmed($response);
    }

    public function releaseConfirmed($response)
    {
        $this->listener->notifyReleaseConfirmed($response);
    }

    public function querySummaryConfirmed($response)
    {
        $this->listener->notifyQuerySummaryConfirmed($response);
    }

    public function queryRecursiveConfirmed($response)
    {
        $this->listener->notifyQueryRecursiveConfirmed($response);
    }

    public function queryNotificationConfirmed($response)
    {
        $this->listener->notifyQueryNotificationConfirmed($response);
    }

    public function queryResultConfirmed($response)
    {
        $this->listener->notifyQueryResultConfirmed($response);
    }

    public function error($response)
    {
        $this->listener->notifyError($response);
    }

    public function errorEvent($response)
    {
        $this->listener->notifyErrorEvent($response);
    }

    public function reserveTimeout($response)
    {
        $this->listener->notifyReserveTimeout($response);
    }
}

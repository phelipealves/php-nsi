<?php

namespace NSI;

interface CallbackReceiver
{
    public function notifyDataPlaneStateChange($receivedResponse);

    public function notifyMessageDeliveryTimeout($receivedResponse);

    public function notifyReserveConfirmed($receivedResponse);

    public function notifyReserveFailed($receivedResponse);

    public function notifyReserveAbortConfirmed($receivedResponse);

    public function notifyReserveCommitConfirmed($receivedResponse);

    public function notifyReserveCommitFailed($receivedResponse);

    public function notifyProvisionConfirmed($receivedResponse);

    public function notifyTerminateConfirmed($receivedResponse);

    public function notifyReleaseConfirmed($receivedResponse);

    public function notifyQuerySummaryConfirmed($receivedResponse);

    public function notifyQueryRecursiveConfirmed($receivedResponse);

    public function notifyQueryNotificationConfirmed($receivedResponse);

    public function notifyQueryResultConfirmed($receivedResponse);

    public function notifyError($receivedResponse);

    public function notifyErrorEvent($receivedResponse);

    public function notifyReserveTimeout($receivedResponse);
}

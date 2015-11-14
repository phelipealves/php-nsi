<?php

namespace NSI;

class BasicCallbackReceiver implements CallbackReceiver
{
    private $requesterClient;

    public function __construct(RequesterClient $requesterClient)
    {
        $this->requesterClient = $requesterClient;
    }

    public function notifyDataPlaneStateChange($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;
        $dataPlaneStatus = [
            'active'            => $receivedResponse->dataPlaneStatus->active,
            'version'           => $receivedResponse->dataPlaneStatus->version,
            'versionConsistent' => $receivedResponse->dataPlaneStatus->versionConsistent,
        ];

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyDataPlaneStateChange received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyMessageDeliveryTimeout($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyMessageDeliveryTimeout received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyReserveConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");

        $this->requesterClient->sendReserveCommit($connectionId);
    }

    public function notifyReserveFailed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;
        $connectionStates = $receivedResponse->connectionStates;
        $serviceException = $receivedResponse->serviceException;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveFailed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyReserveAbortConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveAbortConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyReserveCommitConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveCommitConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");

        $this->requesterClient->sendProvision($connectionId);
    }

    public function notifyReserveCommitFailed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;
        $connectionStates = $receivedResponse->connectionStates;
        $serviceException = $receivedResponse->serviceException;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveCommitFailed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyProvisionConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyProvisionConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyTerminateConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyTerminateConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyReleaseConfirmed($receivedResponse)
    {
        $connectionId = $receivedResponse->connectionId;

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReleaseConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyQuerySummaryConfirmed($receivedResponse)
    {
        $reservation = is_array($receivedResponse->reservation) ? $receivedResponse->reservation :
            [$receivedResponse->reservation];

        $reservations = [];
        foreach ($reservation as $connection) {
            $reservations[] = [
                'connectionId'        => $connection->connectionId,
                'globalReservationId' => $connection->globalReservationId,
                'description'         => $connection->description,
                'startTime'           => $connection->criteria->schedule->startTime,
                'endTime'             => $connection->criteria->schedule->endTime,
                'version'             => $connection->criteria->version,
                'paths'               => $this->getConnPaths($receivedResponse),
            ];
        }

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyQuerySummaryConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    private function getConnPaths($receivedResponse)
    {
        $pathNodes = $receivedResponse->reservation->criteria->children->child;
        if (count($pathNodes) < 2) {
            $pathNodes = [$pathNodes];
        }

        $returnPaths = [];
        foreach ($pathNodes as $pathNode) {
            $pathNodeXml = $pathNode->any;
            $pathNodeXml = str_replace('<nsi_p2p:p2ps>', '<p2p>', $pathNodeXml);
            $pathNodeXml = str_replace('</nsi_p2p:p2ps>', '</p2p>', $pathNodeXml);
            $pathNodeXml = '<?xml version="1.0" encoding="UTF-8"?>'.$pathNodeXml;
            $xml = new \DOMDocument();
            $xml->loadXML($pathNodeXml);
            $parser = new \DOMXpath($xml);
            $src = $parser->query('//sourceSTP');
            $dst = $parser->query('//destSTP');

            $returnPaths[] = [
                'source'      => $src->item(0)->nodeValue,
                'destination' => $dst->item(0)->nodeValue,
            ];
        }

        return $returnPaths;
    }

    public function notifyQueryRecursiveConfirmed($receivedResponse)
    {
        // GET QueryRecursiveConfirmedType
        //$this->callbackReceiverListener->notifyQueryRecursiveConfirmed();

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyQueryRecursiveConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyQueryNotificationConfirmed($receivedResponse)
    {
        // GET QueryNotificationConfirmedType
        //$this->callbackReceiverListener->notifyQueryNotificationConfirmed();

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyQueryNotificationConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyQueryResultConfirmed($receivedResponse)
    {
        // GET QueryResultConfirmedType
        //$this->callbackReceiverListener->notifyQueryResultConfirmed();

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyQueryResultConfirmed received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyError($receivedResponse)
    {
        // GET GenericErrorType
        //$this->callbackReceiverListener->notifyError();

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyError received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyErrorEvent($receivedResponse)
    {
        // GET ErrorEventType
        //$this->callbackReceiverListener->notifyErrorEvent();

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyErrorEvent received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }

    public function notifyReserveTimeout($receivedResponse)
    {
        // GET ReserveTimeoutRequestType
        // QueryType, QueryNotificationType, QueryResultType

        $jsonResponse = json_encode($receivedResponse);

        $message = sprintf('notifyReserveTimeout received: %s', $jsonResponse);
        exec("echo \"$message\" >> /home/bruno/teste.out");
    }
}

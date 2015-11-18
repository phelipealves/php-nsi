<?php

namespace NSI;

use NSI\Model\Circuit;
use NSI\Model\LocalCertificate;
use NSI\Model\Reservation;

class RequesterClient extends \SoapClient
{
    private $connProviderWsdlUri;
    private $callbackServerUri;
    private $localCert;
    private $nsaProvider;
    private $nsaRequester;

    public function __construct($connProviderWsdlUri, $nsaProvider, $nsaRequester, $callBackServerUri = null,
                         LocalCertificate $certificate = null)
    {
        if ($nsaProvider == null || $nsaRequester == null) {
            throw new \Exception('NSA provider and NSA requester must be informed');
        }
        $this->nsaProvider = $nsaProvider;
        $this->nsaRequester = $nsaRequester;

        if ($certificate != null && !($certificate instanceof LocalCertificate)) {
            throw new \Exception('Certificate must be informed like a LocalCertificate object');
        }
        $this->localCert = $certificate;

        $this->connProviderWsdlUri = $connProviderWsdlUri;
        $this->callbackServerUri = $callBackServerUri;

        $isSsl = ($this->localCert == null) ? false : true;
        $streamContextOptions = [
            'ssl' => [
                'verify_peer'       => $isSsl,
                'allow_self_signed' => true,
            ],
            'https' => [
                'curl_verify_ssl_peer'  => $isSsl,
                'curl_verify_ssl_host'  => $isSsl,
            ],
        ];
        if (!$isSsl) {
            $streamContextOptions['ssl']['ciphers'] = 'SHA1';
        }

        $context = stream_context_create($streamContextOptions);
        $soapOptions = [
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'stream_context' => $context,
            'trace'          => 1,
        ];

        if ($isSsl) {
            $soapOptions['local_cert'] = $this->localCert->getCertPath();
            $soapOptions['passphrase'] = $this->localCert->getCertPassphrase();
        }

        parent::__construct($this->connProviderWsdlUri, $soapOptions);
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($request);

        /* Setting namespaces **/
        $dom->documentElement->setAttribute('xmlns:xs', 'http://www.w3.org/2001/XMLSchema');
        $dom->documentElement->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $dom->documentElement->setAttribute('xmlns:gns', 'http://nordu.net/namespaces/2013/12/gnsbod');
        $dom->documentElement->setAttribute('xmlns:saml', 'urn:oasis:names:tc:SAML:2.0:assertion');
        $dom->documentElement->setAttribute('xmlns:type', 'http://schemas.ogf.org/nsi/2013/12/connection/types');
        $dom->documentElement->setAttribute('xmlns:head', 'http://schemas.ogf.org/nsi/2013/12/framework/headers');

        /* Generating Correlation ID */
        $dom->getElementsByTagName('correlationId')->item(0)->nodeValue = 'urn:uuid:'.$this->generateGuid();

        /* Setting prefixes of the elements **/
        $this->changeTag($dom, 'ConnectionTrace', 'gns:ConnectionTrace');
        $this->changeTag($dom, 'reserve', 'type:reserve');
        $this->changeTag($dom, 'reserveCommit', 'type:reserveCommit');
        $this->changeTag($dom, 'provision', 'type:provision');
        $this->changeTag($dom, 'p2ps', 'p2p:p2ps');

        /* Setting the criteria version **/
        $criteriaVersion = 1;
        $criteria = $dom->getElementsByTagName('criteria')->item(0);
        if ($criteria && $criteria != null) {
            for ($i = 0; $i < $criteria->childNodes->length; $i++) {
                $childNode = $criteria->childNodes->item($i);
                if ($childNode->nodeName == 'version') {
                    $criteriaVersion = $childNode->nodeValue;
                    $criteria->removeChild($childNode);
                    break;
                }
            }
        }

        /* Setting attributes **/
        $this->setAttributeByTag($dom, 'Connection', 'index', '0');
        $this->setAttributeByTag($dom, 'criteria', 'version', $criteriaVersion);
        $this->setAttributeByTag($dom, 'parameter', 'type', 'protection');
        $this->setAttributeByTag($dom, 'p2p:p2ps', 'xmlns:p2p',
            'http://schemas.ogf.org/nsi/2013/12/services/point2point');

        /* Set the STP order **/
        if ($nodes = $dom->getElementsByTagName('orderedSTP')) {
            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                $node->setAttribute('order', $i);
            }
        }

        $request = $dom->saveXML();

        return parent::__doRequest($request, $location, $action, $version);
    }

    private function setAggHeader()
    {
        $ns = 'http://schemas.ogf.org/nsi/2013/12/framework/headers';
        $connection = new \SoapVar(['Connection' => $this->nsaRequester], SOAP_ENC_OBJECT, null, null, null, null);

        $headerBody = [
            'protocolVersion' => 'application/vnd.ogf.nsi.cs.v2.provider+soap',
            'correlationId'   => '',
            'requesterNSA'    => $this->nsaRequester,
            'providerNSA'     => $this->nsaProvider,
            'replyTo'         => $this->callbackServerUri,
            'ConnectionTrace' => $connection,
        ];

        $headerBody = new \SoapVar($headerBody, SOAP_ENC_OBJECT, null, null, null, null);
        $header = new \SoapHeader($ns, 'nsiHeader', $headerBody);

        $this->__setSoapHeaders($header);
    }

    private function generateGuid()
    {
        $hexChars = '0123456789abcdef';
        $charsLength = strlen($hexChars);
        $guidText = '';
        for ($i = 1; $i <= 36; $i++) {
            $guidText .= (in_array($i, [9, 14, 19, 24])) ? '-' : $hexChars[rand(0, $charsLength - 1)];
        }

        return $guidText;
    }

    private function changeTag(\DOMDocument $dom, $oldTagName, $newTagName)
    {
        $newNode = $dom->createElement($newTagName);

        if ($oldNode = $dom->getElementsByTagName($oldTagName)->item(0)) {
            $childNodes = $oldNode->childNodes;
            for ($i = 0; $i < $childNodes->length; $i++) {
                $child = $childNodes->item($i);
                $newChild = $child->cloneNode(true);
                $newNode->appendChild($newChild);
            }

            $parent = $oldNode->parentNode;
            $parent->replaceChild($newNode, $oldNode);
        }
    }

    private function setAttributeByTag(\DOMDocument $dom, $tagName, $attName, $attValue)
    {
        if ($nodes = $dom->getElementsByTagName($tagName)) {
            for ($i = 0; $i < $nodes->length; $i++) {
                $node = $nodes->item($i);
                $node->setAttribute($attName, $attValue);
            }
        }
    }

    private function createParametersByReservation(Reservation $reservation)
    {
        $circuit = $reservation->getCircuit();

        $dateTime_UTC = new \DateTime();
        $dateTime_UTC->setTimezone(new \DateTimeZone('UTC'));
        $startTime = $dateTime_UTC->setTimestamp($circuit->getStartTime())->format('Y-m-d\TH:i:s.000-00:00');
        $endTime = $dateTime_UTC->setTimestamp($circuit->getEndTime())->format('Y-m-d\TH:i:s.000-00:00');

        $schedule = [
            'startTime' => $startTime,
            'endTime'   => $endTime,
        ];
        $schedule = new \SoapVar($schedule, SOAP_ENC_OBJECT, null, null, null, null);

        $sourceSTP = $circuit->getSourceUrn().'?vlan='.$circuit->getSourceVlanRequestRange();
        $destSTP = $circuit->getDestinationUrn().'?vlan='.$circuit->getDestinationVlanRequestRange();

        $p2ps = [
            'capacity'       => $circuit->getBandwidth(),
            'directionality' => 'Bidirectional',
            'symmetricPath'  => 'true',
            'sourceSTP'      => $sourceSTP,
            'destSTP'        => $destSTP,
            'parameter'      => 'PROTECTED',
        ];

        $paths = $circuit->getPaths();
        if ($paths != null && count($paths) > 2) {
            $pathsLength = count($paths);
            $waypoints = [];
            for ($i = 1; $i < ($pathsLength - 1); $i++) {
                $stp = $paths[$i];
                $stp = new \SoapVar(['stp' => $stp], SOAP_ENC_OBJECT, null, null, null, null);
                $orderedSTP = new \SoapVar($stp, SOAP_ENC_OBJECT, null, null, 'orderedSTP', null);
                $waypoints[] = $orderedSTP;
            }
            $p2ps[] = new \SoapVar($waypoints, SOAP_ENC_OBJECT, null, null, 'ero', null);
        }

        $p2ps = new \SoapVar($p2ps, SOAP_ENC_OBJECT, null, null, null, null);

        $criteria = [
            'schedule'    => $schedule,
            'serviceType' => 'http://services.ogf.org/nsi/2013/12/descriptions/EVTS.A-GOLE',
            'p2ps'        => $p2ps,
            'version'     => $reservation->getVersion(),
        ];
        $criteria = new \SoapVar($criteria, SOAP_ENC_OBJECT, null, null, null, null);

        $parameters = [
            'globalReservationId' => $reservation->getName(),
            'description'         => $reservation->getDescription(),
            'criteria'            => $criteria,
        ];

        if ($reservation->getConnectionId() != null) {
            $parameters['connectionId'] = $reservation->getConnectionId();
        }

        return $parameters;
    }

    public function getReservationByConnection($connectionId)
    {
        $reservationData = $this->getReservationData($connectionId);
        if ($reservationData === false) {
            throw new \Exception('Could not get the circuit data of connection '.$connectionId);
        }

        $reservationData = $reservationData->reservation;
        if (!isset($reservationData->criteria)) {
            throw new \Exception('The circuit with connection '.$connectionId.' cannot be modified');
        }

        $reservationName = $reservationData->globalReservationId;
        $reservationDescription = $reservationData->description;
        $version = $reservationData->criteria->version;

        $dateTime_UTC = new \DateTime($reservationData->criteria->schedule->startTime);
        $dateTime_UTC->setTimezone(new \DateTimeZone('UTC'));
        $startTime = $dateTime_UTC->getTimestamp();

        $dateTime_UTC = new \DateTime($reservationData->criteria->schedule->endTime);
        $dateTime_UTC->setTimezone(new \DateTimeZone('UTC'));
        $endTime = $dateTime_UTC->getTimestamp();

        $urnAndVlans = $this->extractUrnAndVlans($reservationData->criteria->any);

        $first = true;
        $paths = [];
        foreach ($reservationData->criteria->children as $child) {
            $childUrnAndVlans = $this->extractUrnAndVlans($child->any);

            if ($first) {
                $paths[] = $childUrnAndVlans['source']['urn'].'?vlan='.$childUrnAndVlans['source']['vlan'];
                $first = false;
            }
            $paths[] = $childUrnAndVlans['destination']['urn'].'?vlan='.$childUrnAndVlans['destination']['vlan'];
        }

        $circuit = new Circuit();
        $circuit->setSourceUrn($urnAndVlans['source']['urn']);
        $circuit->setSourceVlanRequestRange($urnAndVlans['source']['vlan']);
        $circuit->setSourceAppliedVlan($urnAndVlans['source']['vlan']);
        $circuit->setDestinationUrn($urnAndVlans['destination']['urn']);
        $circuit->setDestinationVlanRequestRange($urnAndVlans['destination']['vlan']);
        $circuit->setDestinationAppliedVlan($urnAndVlans['destination']['vlan']);
        $circuit->setPaths($paths);
        $circuit->setStartTime($startTime);
        $circuit->setEndTime($endTime);

        $reservation = new Reservation($connectionId, $reservationName, $reservationDescription, $circuit, $version);

        return $reservation;
    }

    private function extractUrnAndVlans($p2pXml)
    {
        $p2pXml = str_replace('<nsi_p2p:p2ps>', '<p2p>', $p2pXml);
        $p2pXml = str_replace('</nsi_p2p:p2ps>', '</p2p>', $p2pXml);
        $p2pXml = '<?xml version="1.0" encoding="UTF-8"?>'.$p2pXml;

        $xml = new \DOMDocument();
        $xml->loadXML($p2pXml);
        $parser = new \DOMXpath($xml);
        $srcStp = $parser->query('//sourceSTP');
        $srcStp = $srcStp->item(0)->nodeValue;
        $dstStp = $parser->query('//destSTP');
        $dstStp = $dstStp->item(0)->nodeValue;

        $sourceUrn = substr($srcStp, 0, strpos($srcStp, '?vlan='));
        $sourceVlan = substr($srcStp, (strpos($srcStp, '?vlan=') + 6));
        $destinationUrn = substr($dstStp, 0, strpos($dstStp, '?vlan='));
        $destinationVlan = substr($dstStp, (strpos($dstStp, '?vlan=') + 6));

        $return = [
            'source' => [
                'urn'  => $sourceUrn,
                'vlan' => $sourceVlan,
            ],
            'destination' => [
                'urn'  => $destinationUrn,
                'vlan' => $destinationVlan,
            ],
        ];

        return $return;
    }

    public function sendReserve(Reservation $reservation)
    {
        $params = $this->createParametersByReservation($reservation);
        $this->setAggHeader();
        try {
            $result = $this->reserve($params);
        } catch (\SoapFault $error) {
            throw $error;
        }

        return $result;
    }

    public function getReservationData($connectionId)
    {
        $params = [
            'connectionId' => $connectionId,
        ];

        $this->setAggHeader();
        try {
            $result = $this->querySummarySync($params);
        } catch (\SoapFault $error) {
            throw $error;
        }

        return $result;
    }

    public function sendTerminate($connectionId)
    {
        $params = [
            'connectionId' => $connectionId,
        ];

        $this->setAggHeader();
        try {
            $result = $this->terminate($params);
        } catch (\SoapFault $error) {
            throw $error;
        }

        return $result;
    }

    public function sendReserveCommit($connectionId)
    {
        $params = [
            'connectionId' => $connectionId,
        ];

        $this->setAggHeader();
        try {
            $result = $this->reserveCommit($params);
        } catch (\SoapFault $error) {
            throw $error;
        }

        return $result;
    }

    public function sendProvision($connectionId)
    {
        $params = [
                'connectionId' => $connectionId,
        ];

        $this->setAggHeader();
        try {
            $result = $this->provision($params);
        } catch (\SoapFault $error) {
            throw $error;
        }

        return $result;
    }
}

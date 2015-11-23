<?php
/**
 * Created by IntelliJ IDEA.
 * User: bruno
 * Date: 11/19/15
 * Time: 8:15 PM
 */

namespace NSI\Common;


class Utils
{
    public static function extractUrnAndVlans($p2pXml)
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

    public static function generateGuid()
    {
        $hexChars = '0123456789abcdef';
        $charsLength = strlen($hexChars);
        $guidText = '';
        for ($i = 1; $i <= 36; $i++) {
            $guidText .= (in_array($i, [9, 14, 19, 24])) ? '-' : $hexChars[rand(0, $charsLength - 1)];
        }

        return $guidText;
    }
}
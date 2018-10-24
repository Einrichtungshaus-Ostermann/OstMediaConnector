<?php declare(strict_types=1);
/**
 * Einrichtungshaus Ostermann GmbH & Co. KG - Media Connector
 *
 * Import Images from external Servers
 *
 * 1.0.0
 * - initial release
 *
 * @package   OstMediaConnector
 *
 * @author    Tim Windelschmidt <tim.windelschmidt@ostermann.de>
 * @copyright 2018 Einrichtungshaus Ostermann GmbH & Co. KG
 * @license   proprietary
 */

namespace OstMediaConnector\Utils;

use RuntimeException;

class Base64Url
{
    public static function encode($arg): string
    {
        return str_replace(['=', '+', '/'], ['', '-', '_'], base64_encode($arg));
    }

    /**
     * @param string $arg
     *
     * @throws \RuntimeException
     *
     * @return bool|string
     */
    public static function decode(string $arg)
    {
        $s = str_replace(['-', '_'], ['+', '/'], $arg);

        switch (\strlen($s) % 4) { // Pad with trailing '='s
            case 0:
                break; // No pad chars in this case
            case 2:
                $s .= '==';
                break; // Two pad chars
            case 3:
                $s .= '=';
                break; // One pad char
            default:
                throw new RuntimeException('Illegal base64url string! - ' . $arg);
        }

        return base64_decode($s);
    }
}

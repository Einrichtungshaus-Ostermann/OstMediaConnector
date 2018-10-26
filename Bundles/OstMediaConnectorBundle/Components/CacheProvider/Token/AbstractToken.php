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

namespace OstMediaConnector\Bundles\OstMediaConnectorBundle\Components\CacheProvider\Token;

use OstMediaConnector\Utils\Base64Url;

abstract class AbstractToken
{
    /**
     * @param array $params
     *
     * @return string
     */
    protected function createToken(array $params): string
    {
        foreach ($params as &$param) {
            $param = '\'' . $param . '\'';
        }
        unset($param);

        $token = implode('.', $params);

        $compressed = gzcompress($token);

        return Base64Url::encode($compressed);
    }

    /**
     * @param string $token
     * @param int|null $expectedSize
     *
     * @throws InvalidTokenException
     *
     * @return array
     */
    protected static function extractToken(string $token, int $expectedSize = null): array
    {
        $compressed = Base64Url::decode($token);

        $tokenString = gzuncompress($compressed);

        if (\strpos($tokenString, '\'') !== 0 || \strrpos($tokenString, '\'') !== \strlen($tokenString) - 1) {
            throw new InvalidTokenException($tokenString);
        }

        $params = explode('\'.\'', $tokenString);

        if (!\is_array($params)) {
            throw new InvalidTokenException($tokenString);
        }

        $paramsLength = \count($params);

        if ($paramsLength !== ($expectedSize ?? $paramsLength)) {
            throw new InvalidTokenException($tokenString);
        }

        foreach ($params as $key => &$param) {
            if ($key === 0) {
                $param .= '\'';
            } elseif ($key === $paramsLength - 1) {
                $param = '\'' . $param;
            } else {
                $param = '\'' . $param . '\'';
            }

            if (\strpos($param, '\'') !== 0 || \strrpos($param, '\'') !== \strlen($param) - 1) {
                throw new InvalidTokenException($tokenString);
            }

            $param = \substr($param, 1, -1);
        }
        unset($param);

        return $params;
    }
}

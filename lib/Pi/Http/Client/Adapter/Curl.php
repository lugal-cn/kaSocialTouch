<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Http\Client\Adapter;

use Zend\Http\Client\Adapter\Curl as ZendCurl;
use Zend\Http\Client\Adapter\Exception as AdapterException;

/**
 * {@inheritDoc}
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Curl extends ZendCurl
{
    /**
     * {@inheritDoc}
     *
     * As of PHP 5.2.0, value must be an array if files are passed to this option with the @ prefix.
     * @see http://www.php.net/curl_setopt
     */
    public function write($method, $uri, $httpVersion = 1.1, $headers = array(), $body = '')
    {
        // Make sure we're properly connected
        if (!$this->curl) {
            throw new AdapterException\RuntimeException("Trying to write but we are not connected");
        }

        if ($this->connectedTo[0] != $uri->getHost() || $this->connectedTo[1] != $uri->getPort()) {
            throw new AdapterException\RuntimeException("Trying to write but we are connected to the wrong host");
        }

        // set URL
        curl_setopt($this->curl, CURLOPT_URL, $uri->__toString());

        // ensure correct curl call
        $curlValue = true;
        switch ($method) {
            case 'GET' :
                $curlMethod = CURLOPT_HTTPGET;
                break;

            case 'POST' :
                $curlMethod = CURLOPT_POST;
                break;

            case 'PUT' :
                // There are two different types of PUT request, either a Raw Data string has been set
                // or CURLOPT_INFILE and CURLOPT_INFILESIZE are used.
                if (is_resource($body)) {
                    $this->config['curloptions'][CURLOPT_INFILE] = $body;
                }
                if (isset($this->config['curloptions'][CURLOPT_INFILE])) {
                    // Now we will probably already have Content-Length set, so that we have to delete it
                    // from $headers at this point:
                    if (!isset($headers['Content-Length'])
                        && !isset($this->config['curloptions'][CURLOPT_INFILESIZE])
                    ) {
                        throw new AdapterException\RuntimeException("Cannot set a file-handle for cURL option CURLOPT_INFILE without also setting its size in CURLOPT_INFILESIZE.");
                    }

                    if (isset($headers['Content-Length'])) {
                        $this->config['curloptions'][CURLOPT_INFILESIZE] = (int) $headers['Content-Length'];
                        unset($headers['Content-Length']);
                    }

                    if (is_resource($body)) {
                        $body = '';
                    }

                    $curlMethod = CURLOPT_UPLOAD;
                } else {
                    $curlMethod = CURLOPT_CUSTOMREQUEST;
                    $curlValue = "PUT";
                }
                break;

            case 'PATCH' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "PATCH";
                break;

            case 'DELETE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "DELETE";
                break;

            case 'OPTIONS' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "OPTIONS";
                break;

            case 'TRACE' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "TRACE";
                break;

            case 'HEAD' :
                $curlMethod = CURLOPT_CUSTOMREQUEST;
                $curlValue = "HEAD";
                break;

            default:
                // For now, through an exception for unsupported request methods
                throw new AdapterException\InvalidArgumentException("Method '$method' currently not supported");
        }

        if (is_resource($body) && $curlMethod != CURLOPT_UPLOAD) {
            throw new AdapterException\RuntimeException("Streaming requests are allowed only with PUT");
        }

        // get http version to use
        $curlHttp = ($httpVersion == 1.1) ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0;

        // mark as HTTP request and set HTTP method
        curl_setopt($this->curl, $curlHttp, true);
        curl_setopt($this->curl, $curlMethod, $curlValue);

        if ($this->outputStream) {
            // headers will be read into the response
            curl_setopt($this->curl, CURLOPT_HEADER, false);
            curl_setopt($this->curl, CURLOPT_HEADERFUNCTION, array($this, "readHeader"));
            // and data will be written into the file
            curl_setopt($this->curl, CURLOPT_FILE, $this->outputStream);
        } else {
            // ensure headers are also returned
            curl_setopt($this->curl, CURLOPT_HEADER, true);

            // ensure actual response is returned
            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        }

        // Treating basic auth headers in a special way
        if (array_key_exists('Authorization', $headers) && 'Basic' == substr($headers['Authorization'], 0, 5)) {
            curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->curl, CURLOPT_USERPWD, base64_decode(substr($headers['Authorization'], 6)));
            unset($headers['Authorization']);
        }

        // set additional headers
        if (!isset($headers['Accept'])) {
            $headers['Accept'] = '';
        }
        $curlHeaders = array();
        foreach ($headers as $key => $value) {
            $curlHeaders[] = $key . ': ' . $value;
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $curlHeaders);

        /**
         * Make sure POSTFIELDS is set after $curlMethod is set:
         * @link http://de2.php.net/manual/en/function.curl-setopt.php#81161
         */
        if ($method == 'POST') {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($curlMethod == CURLOPT_UPLOAD) {
            // this covers a PUT by file-handle:
            // Make the setting of this options explicit (rather than setting it through the loop following a bit lower)
            // to group common functionality together.
            curl_setopt($this->curl, CURLOPT_INFILE, $this->config['curloptions'][CURLOPT_INFILE]);
            curl_setopt($this->curl, CURLOPT_INFILESIZE, $this->config['curloptions'][CURLOPT_INFILESIZE]);
            unset($this->config['curloptions'][CURLOPT_INFILE]);
            unset($this->config['curloptions'][CURLOPT_INFILESIZE]);
        } elseif ($method == 'PUT') {
            // This is a PUT by a setRawData string, not by file-handle
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        } elseif ($method == 'PATCH') {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        }

        // set additional curl options
        if (isset($this->config['curloptions'])) {
            foreach ((array) $this->config['curloptions'] as $k => $v) {
                if (!in_array($k, $this->invalidOverwritableCurlOptions)) {
                    if (curl_setopt($this->curl, $k, $v) == false) {
                        throw new AdapterException\RuntimeException(sprintf("Unknown or erroreous cURL option '%s' set", $k));
                    }
                }
            }
        }

        // send the request

        $response = curl_exec($this->curl);
        // if we used streaming, headers are already there
        if (!is_resource($this->outputStream)) {
            $this->response = $response;
        }

        $request  = curl_getinfo($this->curl, CURLINFO_HEADER_OUT);
        //$request .= $body;
        // @FIXME
        // @see http://www.php.net/curl_setopt
        // As of PHP 5.2.0, value must be an array if files are passed to this option with the @ prefix.
        if (is_array($body)) {
            $request .= http_build_query($body);
        } else {
            $request .= $body;
        }

        if (empty($this->response)) {
            throw new AdapterException\RuntimeException("Error in cURL request: " . curl_error($this->curl));
        }

        // cURL automatically decodes chunked-messages, this means we have to disallow the Zend\Http\Response to do it again
        if (stripos($this->response, "Transfer-Encoding: chunked\r\n")) {
            $this->response = str_ireplace("Transfer-Encoding: chunked\r\n", '', $this->response);
        }

        // cURL can automatically handle content encoding; prevent double-decoding from occurring
        if (isset($this->config['curloptions'][CURLOPT_ENCODING])
            && '' == $this->config['curloptions'][CURLOPT_ENCODING]
            && stripos($this->response, "Content-Encoding: gzip\r\n")
        ) {
            $this->response = str_ireplace("Content-Encoding: gzip\r\n", '', $this->response);
        }

        // Eliminate multiple HTTP responses.
        do {
            $parts  = preg_split('|(?:\r?\n){2}|m', $this->response, 2);
            $again  = false;

            if (isset($parts[1]) && preg_match("|^HTTP/1\.[01](.*?)\r\n|mi", $parts[1])) {
                $this->response    = $parts[1];
                $again              = true;
            }
        } while ($again);

        // cURL automatically handles Proxy rewrites, remove the "HTTP/1.0 200 Connection established" string:
        if (stripos($this->response, "HTTP/1.0 200 Connection established\r\n\r\n") !== false) {
            $this->response = str_ireplace("HTTP/1.0 200 Connection established\r\n\r\n", '', $this->response);
        }

        return $request;
    }
}

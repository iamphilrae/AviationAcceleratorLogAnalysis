<?php
namespace AviationAcceleratorLogAnalysis;

use DateTime;
use Exception;


class LogAnalysis
{
    private string $logDirectory = __DIR__ . "/../logs/";


    /**
     * @param string $filepath
     * @return LogEntry[]|null
     * @throws Exception
     */
    private function parseLogFile(string $filepath): ?array
    {
        $log_entries = [];

        $data = file_get_contents($filepath);
        $data = json_decode($data, false);

        if(is_array($data)) {
            foreach ($data as $d)
            {
                $log = new LogEntry();
                $log->timestamp = new DateTime($d->timestamp);


                /*
                 * Traverse along the test payload, pulling out values as they are found.
                 */
                $payload_traverser = substr($d->textPayload, strpos($d->textPayload, "]") + 2);

                $log->service = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ':')
                );



                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, 'X-Living-Map-Origin') + strlen('X-Living-Map-Origin') + 2);

                $log->sdk_name = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, '/')
                );


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, '/') + 1);

                $log->sdk_version = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ';')
                );



                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, ';') + 1);

                $log->os_platform = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, '/')
                );


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, '/') + 1);

                $log->os_version = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, '"')
                );

                if($log->os_platform !== "Android") { // combine patch versions of the OS version, e.g. 15.1.1 and 15.1.2 into 15.1
                    $version = explode('.', $log->os_version);
                    $log->os_version = $version[0] . "." . (!empty($version[1]) ? $version[1] : '0');
                }


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, '] "') + 3);

                $log->http_method = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ' ')
                );


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, ' ') + 1);

                $log->http_endpoint = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ' ')
                );


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, 'HTTP/') + 10);

                $log->http_response_code = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ' ')
                );


                $payload_traverser = substr($payload_traverser, strpos($payload_traverser, ' - [') + 4);

                $log->ip_address = substr(
                    $payload_traverser,
                    0,
                    strpos($payload_traverser, ' -> ')
                );


                $log_entries[] = $log;
            }
        }

        return empty($log_entries) ? null : $log_entries;
    }


    /**
     * Will process and output data from the latest log file.
     * @throws Exception
     */
    public function latest(): ?array
    {
        return $this->parseLogFile($this->latestLogFilepath());
    }

    /**
     * @param bool $filenameOnly
     * @return string|null
     */
    public function latestLogFilepath(bool $filenameOnly=false): ?string
    {
        $log_files = scandir($this->logDirectory);
        return $filenameOnly ? $log_files[2] : $this->logDirectory . $log_files[2]; // because [0] = "." [1] = ".."
    }


    /**
     * @param LogEntry[] $log_entries
     * @return int
     */
    public static function countUniqueIpAddresses(array $log_entries): int
    {
        $ip_addresses = [];
        foreach($log_entries as $entry)
            $ip_addresses[] = $entry->ip_address;

        return count(array_combine($ip_addresses, $ip_addresses));
    }

    /**
     * @param LogEntry[] $log_entries
     * @return array
     */
    public static function countOsVersions(array $log_entries): array
    {
        $os_versions = [];
        $processed_ip_addresses = [];

        foreach($log_entries as $entry)
        {
            if(!in_array($entry->ip_address, $processed_ip_addresses)) {
                $existing_count = $os_versions[$entry->os_platform][$entry->os_version] ?? 0;
                $os_versions[$entry->os_platform][$entry->os_version] = $existing_count + 1;
                $processed_ip_addresses[] = $entry->ip_address;
            }
        }

        return $os_versions;
    }
}
<?php 
namespace AviationAcceleratorLogAnalysis;


class LogEntry
{
	public \DateTime $timestamp;

    public ?string $service;
    public ?string $ip_address;

    public ?string $http_method;
    public ?string $http_endpoint;
    public ?string $http_response_code;

    public ?string $os_platform;
    public ?string $os_version;

    public ?string $sdk_name;
    public ?string $sdk_version;
}
<?php

namespace Marshmallow\LaravelDatabaseSync\Classes;

use Carbon\Carbon;
use InvalidArgumentException;
use Symfony\Component\Console\Output\OutputInterface;
use Marshmallow\LaravelDatabaseSync\Console\DatabaseSyncCommand;
use Marshmallow\LaravelDatabaseSync\Actions\GetLastSyncDateAction;

class Config
{
    public Carbon $date;
    public bool $debug = false;
    public ?string $multi_tenant_database_type = null;
    public string $remote_temporary_file;
    public string $local_temporary_file;
    public string $cache_file_path;
    public string $cache_file_disk;
    public ?Carbon $sync_start_time = null;
    public ?int $process_timeout;

    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }

    protected function __construct(
        public string $remote_user_and_host,
        public string $remote_database,
        public string $remote_database_username,
        public string $remote_database_password,
        public string $local_host,
        public string $local_database,
        public string $local_database_username,
        public string $local_database_password,
    ) {
        $this->validateParameters();

        $this->remote_temporary_file = config('database-sync.temporary_file_location.remote');
        $this->local_temporary_file = config('database-sync.temporary_file_location.local');
        $this->process_timeout = config('database-sync.process_timeout');

        // MAKE VARIABLE
        $this->cache_file_path = 'marshmallow/database-sync/cache.json';
        $this->cache_file_disk = 'local';
    }

    protected function validateParameters(): void
    {
        if (empty($this->remote_user_and_host)) {
            throw new InvalidArgumentException(__('Remote host cannot be empty'));
        }

        if (empty($this->remote_database)) {
            throw new InvalidArgumentException(__('Remote database cannot be empty'));
        }

        if (empty($this->remote_database_username)) {
            throw new InvalidArgumentException(__('Remote database username cannot be empty'));
        }

        if (empty($this->local_host)) {
            throw new InvalidArgumentException(__('Local host cannot be empty'));
        }

        if (empty($this->local_database)) {
            throw new InvalidArgumentException(__('Local database cannot be empty'));
        }

        if (empty($this->local_database_username)) {
            throw new InvalidArgumentException(__('Local database username cannot be empty'));
        }
    }

    public function isLandlordDatabase(): bool
    {
        return $this->multi_tenant_database_type === 'landlord';
    }

    public function isTenantDatabase(): bool
    {
        return $this->multi_tenant_database_type === 'tenant';
    }
}

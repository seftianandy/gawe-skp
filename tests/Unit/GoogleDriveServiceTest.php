<?php

use App\Services\GoogleDriveService;

test('google drive service reads upload files as strings', function () {
    $service = app(GoogleDriveService::class);
    $filePath = tempnam(sys_get_temp_dir(), 'google-drive-test-');

    try {
        file_put_contents($filePath, 'hello google drive');

        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('readFileContents');
        $method->setAccessible(true);

        $contents = $method->invoke($service, $filePath);

        expect($contents)->toBeString()
            ->and($contents)->toBe('hello google drive');
    } finally {
        @unlink($filePath);
    }
});

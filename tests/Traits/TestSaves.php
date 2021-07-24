<?php
declare(strict_types=1);

namespace Tests\Traits;


use Illuminate\Testing\TestResponse;

trait TestSaves
{
    protected abstract function model();

    protected abstract function routeStore();

    protected abstract function routeUpdate();

    protected function assertStore(array $sendData, array $testDatabaseData, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('POST', $this->routeStore(), $sendData);
        if($response->status() !== 201) {
            throw new \Exception("Response status must be 201, given {$response->status()}:\n {$response->content()}");
        }

        $this->assertInDatabase($response, $testDatabaseData);

        $this->assertJsonResponseContent($response, $testDatabaseData, $testJsonData);

        return $response;
    }

    protected function assertUpdate(array $sendData, array $testDatabaseData, array $testJsonData = null): TestResponse
    {
        /** @var TestResponse $response */
        $response = $this->json('PUT', $this->routeUpdate(), $sendData);
        if($response->status() !== 200) {
            throw new \Exception("Response status must be 200, given {$response->status()}:\n {$response->content()}");
        }

        $this->assertInDatabase($response, $testDatabaseData);

        $this->assertJsonResponseContent($response, $testDatabaseData, $testJsonData);

        return $response;
    }

    private function assertInDatabase(TestResponse $response, array $testDatabaseData)
    {
        $model = $this->model();
        $table = (new $model)->getTable();
        $this->assertDatabaseHas($table, $testDatabaseData + ['id' => $this->getIdFromResponse($response)]);
    }

    private function assertJsonResponseContent(TestResponse $response, array $testDatabaseData, array $testJsonData = null)
    {
        $testResponse = $testJsonData ?? $testDatabaseData;
        $response->assertJsonFragment($testResponse + ['id' => $this->getIdFromResponse($response)]);
    }

    private function getIdFromResponse(TestResponse $response)
    {
        return $response->json('id') ?? $response->json('data.id');
    }
}

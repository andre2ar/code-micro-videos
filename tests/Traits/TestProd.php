<?php


namespace Tests\Traits;


trait TestProd
{
    protected function skipTestIfNotProd(string $message = 'Production test')
    {
        if(!$this->isTestingProd()) {
            $this->markTestSkipped($message);
        }
    }

    protected function isTestingProd(): bool
    {
        return \config('app.testing_prod') !== false;
    }
}

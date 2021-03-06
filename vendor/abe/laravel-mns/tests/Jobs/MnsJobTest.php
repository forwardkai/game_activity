<?php

/*
 * Laravel-Mns -- 阿里云消息队列（MNS）的 Laravel 适配。
 *
 * This file is part of the abe/laravel-mns.
 *
 * (c) Abraham Greyson <82011220@qq.com>
 * @link: https://github.com/abrahamgreyson/laravel-mns
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LaravelMns\Test;

use AliyunMNS\Responses\ReceiveMessageResponse;
use Carbon\Carbon;
use LaravelMns\MnsAdapter;
use Mockery as m;

class MnsJobTest extends AbstractTestCase
{
    public function tearDown()
    {
        parent::tearDown(); // TODO: Change the autogenerated stub
    }

    public function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->default = 'queue';
        $this->receiptHandle = 'ReceiptHandleXXXX';
        $this->delay = 3600;

        $this->mockedJob = 'job';
        $this->mockedData = ['data'];
        $this->mockedPayload = json_encode(['job' => $this->mockedJob, 'data' => $this->mockedData]);

        $this->mockedContainer = m::mock(\Illuminate\Container\Container::class);
        $this->mockedResponse = m::mock(ReceiveMessageResponse::class);
        $this->mockedAdapter = m::mock(MnsAdapter::class);
    }

    public function testFireProperlyCallJobHandler()
    {
        $job = $this->getJob();
        $job->getContainer()->shouldReceive('make')
            ->once()
            ->with('job')
            ->andReturn($handler = m::mock('stdClass'));
        $this->mockedResponse->shouldReceive('getMessageBody')
                             ->andReturn($this->mockedPayload);
        $handler->shouldReceive('fire')
                ->once()
                ->with($job, ['data']);
        $job->fire();
    }

    public function testFireProperlyThrowWhenJobIsNotInsertByLaravel()
    {
        $this->mockedPayload = 'wrong payload.';
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getMessageBody')
                             ->andReturn($this->mockedPayload);
        $this->setExpectedException('InvalidArgumentException');
        $job->fire();
    }

    public function testDeleteProperlyRemovesFromMns()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getReceiptHandle')
                             ->once()
                             ->andReturn($this->receiptHandle);
        $this->mockedAdapter->shouldReceive('deleteMessage')
                            ->with($this->receiptHandle)
                            ->andReturn('true');
        $job->delete($this->receiptHandle);
        $this->assertTrue($job->isDeleted());
    }

    public function testReleaseProperlySetVisibleTimeToMns()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getReceiptHandle')
                             ->twice()
                             ->andReturn($this->receiptHandle);
        $this->mockedAdapter->shouldReceive('changeMessageVisibility')
                            ->once()
                            ->with($this->receiptHandle, $this->delay)
                            ->andReturn('true');
        $job->release($this->delay);
        $this->mockedResponse->shouldReceive('getNextVisibleTime')->once();
        $this->mockedAdapter->shouldReceive('changeMessageVisibility')->once();
        $job->release();
        $this->assertTrue($job->isReleased());
    }

    public function testAttemptsCanGetDequeueCount()
    {
        $job = $this->getJob();
        $this->mockedResponse->shouldReceive('getDequeueCount')->andReturn(5);
        $attempts = $job->attempts();
        $this->assertEquals(5, $attempts);
    }

    private function getJob()
    {
        return new \LaravelMns\Jobs\MnsJob(
            $this->mockedContainer,
            $this->mockedAdapter,
            $this->default,
            $this->mockedResponse
        );
    }
}

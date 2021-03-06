<?php

use Illuminate\Container\Container;
use Mockery as m;

class QueueBeanstalkdJobTest extends \L4\Tests\BackwardCompatibleTestCase {

	public function tearDown()
	{
		m::close();
	}


	public function testFireProperlyCallsTheJobHandler()
	{
		$job = $this->getJob();
		$job->getPheanstalkJob()->shouldReceive('getData')->once()->andReturn(json_encode(array('job' => 'foo', 'data' => array('data'))));
		$job->getContainer()->shouldReceive('make')->once()->with('foo')->andReturn($handler = m::mock('StdClass'));
		$handler->shouldReceive('fire')->once()->with($job, array('data'));

		$job->fire();
	}


	public function testDeleteRemovesTheJobFromBeanstalkd()
	{
		$job = $this->getJob();
		$job->getPheanstalk()->shouldReceive('delete')->once()->with($job->getPheanstalkJob());

		$job->delete();
	}


	public function testReleaseProperlyReleasesJobOntoBeanstalkd()
	{
		$job = $this->getJob();
		$job->getPheanstalk()->shouldReceive('release')->once()->with($job->getPheanstalkJob(), \Pheanstalk\PheanstalkInterface::DEFAULT_PRIORITY, 0);

		$job->release();
	}


	public function testBuryProperlyBuryTheJobFromBeanstalkd()
	{
		$job = $this->getJob();
		$job->getPheanstalk()->shouldReceive('bury')->once()->with($job->getPheanstalkJob());

		$job->bury();
	}


	protected function getJob()
	{
		return new Illuminate\Queue\Jobs\BeanstalkdJob(
			m::mock(Container::class),
			m::mock(\Pheanstalk\Pheanstalk::class),
			m::mock(\Pheanstalk\Job::class),
			'default'
		);
	}

}

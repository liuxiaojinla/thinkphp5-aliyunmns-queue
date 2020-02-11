<?php
namespace think\queue\job;

use AliyunMNS\Responses\ReceiveMessageResponse;
use think\queue\connector\AliyunMNS as AliyunMNSQueue;
use think\queue\Job;

class AliyunMNS extends Job{

	/**
	 * The Iron queue instance.
	 *
	 * @var AliyunMNSQueue
	 */
	protected $aliyunmns;

	/**
	 * The IronMQ message instance.
	 *
	 * @var object
	 */
	protected $job;

	/**
	 * AliyunMNS constructor.
	 *
	 * @param \think\queue\connector\AliyunMNS $aliyunmns
	 * @param ReceiveMessageResponse           $job
	 * @param string                           $queue
	 */
	public function __construct(AliyunMNSQueue $aliyunmns, $job, $queue){
		$this->aliyunmns = $aliyunmns;
		$this->job = $job;
		$this->queue = $queue;
	}

	/**
	 * Fire the job.
	 *
	 * @return void
	 */
	public function fire(){
		$this->resolveAndFire(json_decode($this->getRawBody(), true));
	}

	/**
	 * Get the number of times the job has been attempted.
	 *
	 * @return int
	 */
	public function attempts(){
		return (int)$this->job->getDequeueCount();
	}

	/**
	 * 删除任务
	 */
	public function delete(){
		parent::delete();

		$this->aliyunmns->deleteMessage($this->queue, $this->job->getReceiptHandle());
	}

	/**
	 * 重新发布任务
	 *
	 * @param int $delay
	 */
	public function release($delay = 0){
		parent::release($delay);

		$this->delete();

		$this->aliyunmns->release($this->queue, $this->job, $delay);
	}

	/**
	 * Get the raw body string for the job.
	 *
	 * @return string
	 */
	public function getRawBody(){
		return $this->job->getMessageBody();
	}

}

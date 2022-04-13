<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidSuccessEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $msg;

    /**
     *  定义 msg 变量，保存弹幕消息
     */
    public function __construct(string $msg)
    {
        // 简单的消息列表
        $this->msg = $msg;
    }

    /**
     *  弹幕所有人都可以收到，所以返回公共频道就 OK 的
     */
    public function broadcastOn(): Channel
    {
        return new Channel('auction');
    }

    /**
     *  重命名一下广播名称，一般默认为类名
     */
    public function broadcastAs(): string
    {
        return 'auction.msg';
    }
}

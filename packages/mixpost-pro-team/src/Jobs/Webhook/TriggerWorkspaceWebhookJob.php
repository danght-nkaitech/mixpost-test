<?php

namespace Inovector\Mixpost\Jobs\Webhook;

use Inovector\Mixpost\Contracts\QueueWorkspaceAware;

class TriggerWorkspaceWebhookJob extends TriggerSystemWebhookJob implements QueueWorkspaceAware {}

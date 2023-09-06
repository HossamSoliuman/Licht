<?php

namespace Hossam\Licht\Controllers;

use Hossam\Licht\Traits\ApiResponse;
use Hossam\Licht\Traits\ManagesFiles;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;


class LichtBaseController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ApiResponse, ManagesFiles;
}

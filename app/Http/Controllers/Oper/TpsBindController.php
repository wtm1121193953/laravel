<?php

namespace App\Http\Controllers\Oper;

//use App\Exceptions\DataNotFoundException;
use App\Http\Controllers\Controller;
use App\Result;

class TpsBindController extends Controller{
	
	public function getBindInfo(){
		
		return Result::success([
				'bindAccount' => '',
		]);
		//throw new DataNotFoundException('数据异常！');
		
	}
	
	public function bindAccount(){
		return Result::success([
				'bindAccount' => 'test',
		]);
	}
}
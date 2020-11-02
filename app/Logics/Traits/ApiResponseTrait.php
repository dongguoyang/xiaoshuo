<?php
namespace App\Logics\Traits;

trait ApiResponseTrait {
	protected $err_code = 0;
	protected $err_msg = 'ok';
	protected $data = null;
	protected $header = [];
	protected $response;

	public function __set($name, $value) {
		// TODO: Implement __set() method.
		$this->$name = $value;
	}
	/**
	 * 带状态码的返回结果
	 * @param mixed $data
	 * @param int $err_code
	 * @param  string $err_msg
	 */
	public function result($data, $err_code = 0, $err_msg = 'ok') {
	    if ($err_code > 0 && isset($data['file']) && isset($data['line'])) {
	        $data = [];
        }
		$this->response = [
			'data' => $data ?: $this->data,
			'err_code' => $err_code ?: $this->err_code,
			'err_msg' => $err_msg ?: $this->err_msg,
		];

		return $this->send('json');
	}
	/**
	 * 直接返回结果
	 * @param fixed $data
	 */
	public function returnData($data) {
		$this->response = $data ?: $this->data;

		return $this->send('json');
	}
	/**
	 * 生成返回对象
	 * @param string $type
	 * @param int $status
	 */
	public function send($type, $status = 200) {
		if ($type == 'json') {
			return response()->json($this->response)->withHeaders($this->header);
		} else {
			return response($this->response, $status, $this->header);
		}
	}
}

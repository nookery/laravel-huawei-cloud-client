<?php

namespace HuaweiCloud\Contracts;

use Illuminate\Support\Collection;

class Response
{
    /**
     * 请求ID，每次都不同
     *
     * @var
     */
    protected $request_id;

    /**
     * HTTP响应状态码，一组从1xx到5xx的数字代码，
     * 详细文档：https://support.huaweicloud.com/api-bpconsole/zh-cn_topic_0075212346.html
     *
     * @var
     */
    protected $status_code = 0;

    /**
     * 错误码，当接口调用出错时，会返回错误码及错误信息说明
     *
     * @var
     */
    protected $error_code = 0;

    /**
     * 错误描述信息，当接口调用出错时，会返回错误码及错误信息说明
     *
     * @var
     */
    protected $error_msg = '';

    /**
     * API调用返回的响应消息体，不同API有不同的返回
     *
     * @var
     */
    protected $result = null;

    /**
     * 获取属性
     *
     * @param $attribute
     * @return |null
     */
    public function get($attribute)
    {
        return isset($this->$attribute) ? $this->$attribute : null;
    }

    /**
     * 设置属性
     *
     * @param $attribute
     * @param $value
     * @return |null
     */
    public function set($attribute, $value)
    {
        return isset($this->$attribute) ? $this->$attribute = $value : false;
    }

    /**
     * 设置HTTP响应状态码
     *
     * @param integer $value
     * @return $this
     */
    public function setStatusCode($value = 0)
    {
        $this->status_code = $value;

        return $this;
    }

    /**
     * 设置错误码
     *
     * @param integer $value
     * @return $this
     */
    public function setErrorCode($value = 0)
    {
        $this->error_code = $value;

        return $this;
    }

    /**
     * 设置错误信息
     *
     * @param string $value
     * @return $this
     */
    public function setErrorMessage($value = '')
    {
        $this->error_msg = $value;

        return $this;
    }

    /**
     * 设置响应消息体
     *
     * @param Collection $value
     * @return $this
     */
    public function setResult(Collection $value)
    {
        $this->result = $value;

        return $this;
    }

    /**
     * 设置请求ID
     *
     * @param string $value
     * @return $this
     */
    public function setRequestId($value = '')
    {
        $this->request_id = $value;

        return $this;
    }
}

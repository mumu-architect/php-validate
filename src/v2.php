<?php



class Validate2
{

    /**
     * 当前验证规则
     * @var array
     */
    protected $rule = [];

    /**
     * 验证提示信息
     * @var array
     */
    protected $message = [];


    /**
     * 错误信息
     * @var array
     * */
    protected $error = [];

    /**
     * 验证正则定义
     * @var array
     */
    protected $regex = [];

    /**
     * 内置正则验证规则
     * @var array
     * @example
     * alpha                 纯字母
     * alphaNum              字母和数字
     * alphaDash             字母和数字，下划线_及破折号-
     * chs                   汉字
     * chsAlpha              汉字、字母
     * chsAlphaNum           汉字、字母和数字
     * chsDash               汉字、字母、数字和下划线_及破折号-
     * idCard                身份证格式
     * zip                   邮政编码
     */
    protected $defaultRegex = [
        'alpha'       => '/^[A-Za-z]+$/',
        'alphaNum'    => '/^[A-Za-z0-9]+$/',
        'alphaDash'   => '/^[A-Za-z0-9\-\_]+$/',
        'chs'         => '/^[\x{4e00}-\x{9fa5}]+$/u',
        'chsAlpha'    => '/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u',
        'chsAlphaNum' => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u',
        'chsDash'     => '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\_\-]+$/u',
        'mobile'      => '/^1[3-9]\d{9}$/',
        'idCard'      => '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/',
        'zip'         => '/\d{6}/',
    ];

    /**
     * Filter_var 规则
     * @var array
     */
    protected $filter = [
        'email'   => FILTER_VALIDATE_EMAIL,
        'ip'      => [FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6],
        'integer' => FILTER_VALIDATE_INT,
        'url'     => FILTER_VALIDATE_URL,
        'macAddr' => FILTER_VALIDATE_MAC,
        'float'   => FILTER_VALIDATE_FLOAT,
    ];

    /**
     * 验证规则
     * */
    public function rule($rule){
        $this->rule = $rule;
        return $this;
    }
    /**
     * 设置提示信息
     * @access public
     * @param array $message 错误信息
     * @return Validate
     */
    public function message(array $message)
    {
        $this->message = array_merge($this->message, $message);
        return $this;
    }

    /**
     * 数据自动验证
     * @access public
     * @param array $data  数据
     * @param array $rules 验证规则
     * @return bool false:验证未通过，返回错误提示；true：验证通过，无错误提示
     */
    public function check($data){
        $rule = $this->rule;
        $message = $this->message;
        // 判断数据是否为字符串
        if(!is_array($data)) $this->error = ['error'=>'验证数据必须为数组格式'];
        foreach ($rule as $rules=>$val){
            $this->checkItem($rules,$val,$data[$rules],$message[$rules]);
        }
        return  empty($this->error)?true:false;
    }

    /**
     * 获取所有错误信息
     * */
    private function getAllError(){
        return $this->error;
    }
    /**
     * 抛出错误信息
     * */
    public function getError(){
        // 取第一个数组中的第一个值
        $msg = array_shift(array_shift($this->error));
        return $msg;
    }

    // 验证器的原始结构
    private function checkOne(){
        $data = [
            'name'=>1,
        ];
        $message = ['require'=>'名称不能为空','number'=>'必须为数字','max'=>'最大值为12','min'=>'最小值为4'];
        $rule = ['require'=>'','number'=>'','max'=>12,'min'=>4];
        $this->checkItem('name',$rule,$data['name'],$message);
    }

    /**
     * 验证单个字段规则
     * 需要验证的字段，启用某条规则，验证字段的限定值，它的提示信息
     * @access protected
     * @param string $field 字段名
     * @param mixed  $rules 验证规则
     * @param mixed  $value 字段值
     * @param array  $message   提示信息
     * @return mixed
     */
    public function checkItem(string $field,$rules,$value,$message ='')
    {
        foreach($rules as $k=>$v){
            $result = $this->is($k,$value,$v);
            if($result ===false){
                if(isset($message[$k])){
                    $errorMsg = $message[$k];
                }else{
                    $errorMsg = "请设置字段 {$field} 的错误提示";
                }
                $this->error[$field][] = $errorMsg;
            }
        }
        return $this->error;
    }

    /**
     * 验证字段值是否为有效格式
     * @access public
     * @param string $rule  验证规则
     * @param mixed  $value 字段值
     * @param array  $data  验证规则限定的值
     * @return bool
     */
    public function is(string $rule,$value, $data = '')
    {
        $result = false;
        switch ($rule){
            case 'require':
                // 必须有参数
                if(!empty($value)){
                    $result = true;
                }
                break;
            case 'number':
                // 必须为数字
                if(is_numeric($value)) $result = true;
                break;
            case 'length':
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                if(is_array($value)){
                    $length = count($value);
                }else{
                    $length = mb_strlen((string) $value);
                }
                if($length>=$data[0] && $length<=$data[1]) $result = true;
                break;
            case 'max':
                if($data >= $value  ) $result = true;
                break;
            case 'min':
                if($data <= $value) $result = true;
                break;
            case 'between':
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                if($value >= $data[0] && $value <= $data[1]) $result = true;
                break;
            case 'notbetween':
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                if($value < $data[0] || $value > $data[1]) $result = true;
                break;
            case 'in':
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                if(in_array($value,$data)) $result = true;
                break;
            case 'notin':
                if (is_string($data)) {
                    $data = explode(',', $data);
                }
                if(!in_array($value,$data)) $result = true;
                break;
            case 'confirm':
                if($value ==$data) $result = true;
                break;
            default:
                // 调用其他验证规则
                if (isset($this->filter[$rule])) {
                    // Filter_var验证规则
                    $result = $this->filter($value, $this->filter[$rule]);
                } else {
                    // 正则验证
                    $result = $this->regex($value, $rule);
                }
        }
        return $result;
    }

    /**
     * 使用正则验证数据
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则 正则规则或者预定义正则名
     * @return bool
     */
    public function regex($value, $rule): bool
    {
        if (isset($this->regex[$rule])) {
            $rule = $this->regex[$rule];
        } elseif (isset($this->defaultRegex[$rule])) {
            $rule = $this->defaultRegex[$rule];
        }

        if (is_string($rule) && 0 !== strpos($rule, '/') && !preg_match('/\/[imsU]{0,4}$/', $rule)) {
            // 不是正则表达式则两端补上/
            $rule = '/^' . $rule . '$/';
        }

        return is_scalar($value) && 1 === preg_match($rule, (string) $value);
    }

    /**
     * 使用filter_var方式验证
     * @access public
     * @param mixed $value 字段值
     * @param mixed $rule  验证规则
     * @return bool
     */
    public function filter($value, $rule): bool
    {
        if (is_string($rule) && strpos($rule, ',')) {
            [$rule, $param] = explode(',', $rule);
        } elseif (is_array($rule)) {
            $param = $rule[1] ?? null;
            $rule  = $rule[0];
        } else {
            $param = null;
        }

        return false !== filter_var($value, is_int($rule) ? $rule : filter_id($rule), $param);
    }

}

public function v2(){
    $data = [
        'name'=>'s',
        'title'=>'222',
        'num'=>'s',
        'mobile'=>'1371448298s',
        'card' =>'421182198910062135f',
        'email' =>'21123ddf@qq.com',
        'age' =>55,
        'age2' =>55,
        'sex' =>3
    ];

    $rule = [
        'name' =>['require'=>'','number'=>'','max'=>12,'min'=>4],
        'title' =>[
            # 两种写法都可以
            'length'=>'3,5'
            //'length'=>[3,5]
        ],
        'num'=>['require'=>'','number'=>'','max'=>10],
        'mobile' => ['mobile'=>''],
        'card' => ['idCard'=>''],
        'email'=>['email'=>''],
        'age'=>[
            //'between'=>[18,40],
            'between'=>'18,40',
        ],
        'age2'=>[
            'notbetween'=>'18,40',
        ],
        'sex'=>[
            'in'=>[1,2]
        ]
    ];
    $message = [
        'name' => ['require'=>'名称不能为空','number'=>'必须为数字','max'=>'最大值为12','min'=>'最小值为4'],
        'title'=>['length'=>'长度超出限定范围3-5'],
        'num'=>['require'=>'不能为空数据','number'=>'必须为数字','max'=>'最大值不能超过10'],
        'mobile'=>['mobile'=>'手机号码不正确'],
        'card'=>['idCard'=>'身份证号码不正确'],
        'email'=>['email'=>'邮箱不正确'],
        'age'=> ['between'=>'年龄必须在18到40之间'],
        'age2'=> ['notbetween'=>'触发了notbetween效果'],
        //'sex'=>['in'=>'性别必须在1-2之间']
    ];
    $validate = new Validate2();
    $validateResult = $validate->rule($rule)->message($message)->check($data);
    if($validateResult !=true){
        $msg = $validate->getError();
        print_r($msg);
    }

}

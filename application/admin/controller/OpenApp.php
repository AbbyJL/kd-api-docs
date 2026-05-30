
<?php
namespace app\admin\controller;

use think\Db;

/**
 * 开放应用管理
 */
class OpenApp extends Base
{
    /**
     * 应用列表
     */
    public function index()
    {
        $page = input('page', 1);
        $pageSize = input('pageSize', 20);
        $keyword = input('keyword', '');

        $where = [];
        if ($keyword) {
            $where['app_name|app_key'] = ['like', '%' . $keyword . '%'];
        }

        $list = Db::name('open_apps')
            -&gt;where($where)
            -&gt;order('id desc')
            -&gt;page($page, $pageSize)
            -&gt;select();

        $total = Db::name('open_apps')-&gt;where($where)-&gt;count();

        $this-&gt;assign('list', $list);
        $this-&gt;assign('total', $total);
        $this-&gt;assign('page', $page);
        $this-&gt;assign('pageSize', $pageSize);
        $this-&gt;assign('keyword', $keyword);

        return $this-&gt;fetch();
    }

    /**
     * 添加/编辑应用
     */
    public function add()
    {
        $id = input('id', 0);
        $info = [];

        if ($id) {
            $info = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;find();
        }

        $this-&gt;assign('info', $info);
        return $this-&gt;fetch();
    }

    /**
     * 保存应用
     */
    public function save()
    {
        $id = input('id', 0);
        $appName = input('appName', '');
        $userName = input('userName', '');
        $mobile = input('mobile', '');
        $remark = input('remark', '');
        $status = input('status', 1);

        if (empty($appName)) {
            $this-&gt;error('应用名称不能为空');
        }

        $data = [
            'app_name' =&gt; $appName,
            'user_name' =&gt; $userName,
            'mobile' =&gt; $mobile,
            'remark' =&gt; $remark,
            'status' =&gt; $status,
            'update_time' =&gt; time()
        ];

        if ($id) {
            $result = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;update($data);
            $msg = '更新';
        } else {
            $appKey = 'AK' . date('YmdHis') . rand(1000, 9999);
            $appSecret = md5($appKey . time() . rand_string(16));
            
            $data['app_key'] = $appKey;
            $data['app_secret'] = $appSecret;
            $data['create_time'] = time();
            
            $result = Db::name('open_apps')-&gt;insert($data);
            $msg = '添加';
        }

        if ($result !== false) {
            $this-&gt;success($msg . '成功', url('index'));
        } else {
            $this-&gt;error($msg . '失败');
        }
    }

    /**
     * 重置密钥
     */
    public function resetSecret()
    {
        $id = input('id', 0);

        if (!$id) {
            $this-&gt;error('参数错误');
        }

        $appInfo = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;find();
        if (empty($appInfo)) {
            $this-&gt;error('应用不存在');
        }

        $newSecret = md5($appInfo['app_key'] . time() . rand_string(16));
        $result = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;update([
            'app_secret' =&gt; $newSecret,
            'update_time' =&gt; time()
        ]);

        if ($result !== false) {
            $this-&gt;success('重置成功');
        } else {
            $this-&gt;error('重置失败');
        }
    }

    /**
     * 删除应用
     */
    public function delete()
    {
        $id = input('id', 0);

        if (!$id) {
            $this-&gt;error('参数错误');
        }

        $result = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;delete();

        if ($result !== false) {
            $this-&gt;success('删除成功');
        } else {
            $this-&gt;error('删除失败');
        }
    }

    /**
     * 查看密钥
     */
    public function showSecret()
    {
        $id = input('id', 0);

        if (!$id) {
            $this-&gt;error('参数错误');
        }

        $appInfo = Db::name('open_apps')-&gt;where(['id' =&gt; $id])-&gt;find();
        if (empty($appInfo)) {
            $this-&gt;error('应用不存在');
        }

        return json([
            'code' =&gt; 0,
            'data' =&gt; [
                'appKey' =&gt; $appInfo['app_key'],
                'appSecret' =&gt; $appInfo['app_secret']
            ]
        ]);
    }

    /**
     * 请求日志
     */
    public function logs()
    {
        $page = input('page', 1);
        $pageSize = input('pageSize', 20);
        $appId = input('appId', 0);

        $where = [];
        if ($appId) {
            $where['app_id'] = $appId;
        }

        $list = Db::name('open_api_logs')
            -&gt;alias('l')
            -&gt;join('open_apps a', 'l.app_id = a.id', 'left')
            -&gt;where($where)
            -&gt;field('l.*, a.app_name')
            -&gt;order('l.id desc')
            -&gt;page($page, $pageSize)
            -&gt;select();

        $total = Db::name('open_api_logs')-&gt;where($where)-&gt;count();
        $apps = Db::name('open_apps')-&gt;select();

        $this-&gt;assign('list', $list);
        $this-&gt;assign('total', $total);
        $this-&gt;assign('page', $page);
        $this-&gt;assign('pageSize', $pageSize);
        $this-&gt;assign('appId', $appId);
        $this-&gt;assign('apps', $apps);

        return $this-&gt;fetch();
    }
}

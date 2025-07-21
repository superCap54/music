<?php

namespace Core\Users\Models;

use CodeIgniter\Model;

class UserWorkflowsModel extends Model
{
    protected $table = TB_USERWORKFLOWS;
    protected $primaryKey = 'user_workflow_id';

    // 指定允许批量赋值的字段
    protected $allowedFields = [
        'user_id',
        'team_id',
        'workflow_id',
        'workflow_name',
        'title',
        'descript',
        'category',
        'tags',
        'custom_data',
        'is_enabled',
        'schedule_type',
        'schedule_time',
        'schedule_days',
        'schedule_date',
        'last_run_at',
        'next_run_at',
        'is_processing',
        'created_at',
        'updated_at'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function __construct()
    {
        $this->config = parse_config(include realpath(__DIR__ . "/../Config.php"));
    }

    public function get_list($user_id, $workflow_id = null, $get_workflows_detail = false)
    {
        // 确保总是返回数组
        $this->asArray();

        $builder = $this->where('sp_user_workflows.user_id', $user_id);

        if ($workflow_id !== null) {
            $builder->where('sp_user_workflows.workflow_id', $workflow_id);
        }

        // 如果需要获取工作流详情
        if ($get_workflows_detail) {
            $builder->select('sp_user_workflows.*, sp_workflows.*')
                ->join('sp_workflows', 'sp_workflows.workflow_id = sp_user_workflows.workflow_id');
        }

        // 使用 try-catch 捕获可能的数据库错误
        try {
            $result = $builder->orderBy('sp_user_workflows.created_at', 'DESC')->findAll();

            // 处理结果，确保返回格式一致
            if ($get_workflows_detail) {
                // 合并两个表的数据，避免字段冲突
                return array_map(function ($item) {
                    $workflow_data = [];
                    $user_workflow_data = [];

                    // 分离两个表的数据
                    foreach ($item as $key => $value) {
                        if (strpos($key, 'workflows.') === 0) {
                            $workflow_data[substr($key, 10)] = $value;
                        } else {
                            $user_workflow_data[$key] = $value;
                        }
                    }

                    return $user_workflow_data;
                }, is_array($result) ? $result : []);
            }

            return is_array($result) ? $result : [];

        } catch (\Exception $e) {
            log_message('error', 'Failed to get workflows: ' . $e->getMessage());
            return [];
        }
    }


    public function lock_workflow($id)
    {
        $this->builder()
            ->where('user_workflow_id', $id)
            ->where('is_processing', 0)
            ->update(['is_processing' => 1]);
    }

    public function unlock_workflow($id)
    {
        $this->builder()
            ->where('user_workflow_id', $id)
            ->update(['is_processing' => 0]);
    }

    public function update_workflow_after_run($id, $nextRun)
    {
        $data = [
            'last_run_at' => date('Y-m-d H:i:s'),
            'next_run_at' => $nextRun,
            'is_processing' => 0
        ];

        $this->builder()
            ->where('user_workflow_id', $id)
            ->update($data);
    }

    public function get_due_workflows($limit = 5)
    {
        $now = date('Y-m-d H:i:s');

        $builder = $this->builder();
        $builder->select('*');
        $builder->where('is_enabled', 2); // 启用状态
        $builder->where('next_run_at <=', $now);
        $builder->where('is_processing', 0); // 未在处理中
        $builder->orderBy('next_run_at', 'ASC'); // 先处理最早到期任务
        $builder->limit($limit); // 关键：限制每次处理数量

        $query = $builder->get();
        return $query->getResult();
    }

    // 在update_workflow_after_run()后添加新方法
    public function cleanup_stale_workflows()
    {
        $timeout = date('Y-m-d H:i:s', time() - 3600); // 1小时未更新视为超时
        $this->builder()
            ->where('is_processing', 1)
            ->where('updated_at <', $timeout)
            ->update(['is_processing' => 0]);
    }
}

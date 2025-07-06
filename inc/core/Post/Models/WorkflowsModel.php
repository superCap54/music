<?php
namespace Core\Post\Models;

use CodeIgniter\Model;
use Config\Database;

class WorkflowsModel extends Model
{
    // 工作流模板表配置
    protected $workflowsTable = 'sp_workflows';
    protected $workflowPrimaryKey = 'workflow_id';

    // 用户工作流关联表配置
    protected $userWorkflowsTable = 'sp_user_workflows';
    protected $userWorkflowPrimaryKey = 'user_workflow_id';

    protected $allowedUserWorkflowFields = [
        'user_id', 'workflow_id', 'is_enabled', 'custom_config'
    ];

    public function __construct()
    {
        parent::__construct();

        // 获取数据库连接
        $this->db = Database::connect();
    }

    /**
     * ====================================
     * 工作流模板表(workflows)操作
     * ====================================
     */

    /**
     * 创建新工作流模板
     *
     * @param array $data 包含工作流数据
     * @return int|false 新插入的ID或false
     */
    public function createWorkflow(array $data): int|false
    {
        // 准备基础数据
        $workflowData = [
            'make_id' => $data['make_id'] ?? '',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'image_url' => $data['image_url'] ?? '',
            'config_schema' => isset($data['config_schema']) ?
                json_encode($data['config_schema'], JSON_UNESCAPED_UNICODE) : '[]',
            'is_active' => $data['is_active'] ?? 1
        ];

        // 执行插入
        $this->db->table($this->workflowsTable)->insert($workflowData);

        // 返回新插入的ID
        return $this->db->insertID();
    }

    /**
     * 获取所有工作流模板
     *
     * @param bool $activeOnly 是否只获取激活的工作流
     * @return array 工作流数组
     */
    public function getAllWorkflows(bool $activeOnly = true): array
    {
        try {
            $builder = $this->db->table($this->workflowsTable);

            if ($activeOnly) {
                $builder->where('is_active', 1);
            }

            $query = $builder->get();

            // 添加错误检查
            if ($query === false) {
                log_message('error', '获取工作流失败: ' . $this->db->error()['message']);
                return [];
            }

            $results = $query->getResultArray();

            // 解码JSON字段
            foreach ($results as &$row) {
                if (!empty($row['config_schema'])) {
                    $row['config_schema'] = json_decode($row['config_schema'], true);
                }
            }

            return $results;

        } catch (\Exception $e) {
            log_message('error', '获取工作流异常: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * 通过ID获取工作流模板
     *
     * @param int $id 工作流ID
     * @return array|null 工作流数据或null
     */
    public function getWorkflowById(int $id): ?array
    {
        $builder = $this->db->table($this->workflowsTable);
        $builder->where($this->workflowPrimaryKey, $id);
        $query = $builder->get();

        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // 解码JSON字段
        if (!empty($result['config_schema'])) {
            $result['config_schema'] = json_decode($result['config_schema'], true);
        }

        return $result;
    }

    /**
     * 通过make_id获取工作流
     *
     * @param string $makeId make_id标识符
     * @return array|null 工作流数据或null
     */
    public function getWorkflowByMakeId(string $makeId): ?array
    {
        $builder = $this->db->table($this->workflowsTable);
        $builder->where('make_id', $makeId);
        $query = $builder->get();

        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // 解码JSON字段
        if (!empty($result['config_schema'])) {
            $result['config_schema'] = json_decode($result['config_schema'], true);
        }

        return $result;
    }

    /**
     * 更新工作流模板
     *
     * @param int $id 工作流ID
     * @param array $data 更新数据
     * @return bool 是否成功
     */
    public function updateWorkflow(int $id, array $data): bool
    {
        // 准备可更新字段
        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['image_url'])) $updateData['image_url'] = $data['image_url'];
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];

        // 处理JSON字段
        if (isset($data['config_schema'])) {
            $updateData['config_schema'] = json_encode(
                $data['config_schema'],
                JSON_UNESCAPED_UNICODE
            );
        }

        // 如果没有数据更新则返回
        if (empty($updateData)) {
            return false;
        }

        // 执行更新
        $builder = $this->db->table($this->workflowsTable);
        $builder->where($this->workflowPrimaryKey, $id);
        return $builder->update($updateData);
    }

    /**
     * 删除工作流模板
     *
     * @param int $id 工作流ID
     * @return bool 是否成功
     */
    public function deleteWorkflow(int $id): bool
    {
        $builder = $this->db->table($this->workflowsTable);
        $builder->where($this->workflowPrimaryKey, $id);
        return $builder->delete();
    }

    /**
     * ====================================
     * 用户工作流关联表(sp_user_workflows)操作
     * ====================================
     */

    /**
     * 为用户创建工作流配置
     *
     * @param int $userId 用户ID
     * @param int $workflowId 工作流模板ID
     * @param array $customConfig 自定义配置数据
     * @param bool $isEnabled 是否启用
     * @return int|false 新创建的用户工作流ID
     */
    public function createUserWorkflow(
        int $userId,
        int $workflowId,
        array $customConfig = [],
        bool $isEnabled = true
    ): int|false
    {
        // 检查是否已存在关联
        if ($this->getUserWorkflow($userId, $workflowId)) {
            return false; // 已存在关联
        }

        $configJson = json_encode($customConfig, JSON_UNESCAPED_UNICODE);

        $data = [
            'user_id' => $userId,
            'workflow_id' => $workflowId,
            'is_enabled' => $isEnabled,
            'custom_config' => $configJson
        ];

        $this->db->table($this->userWorkflowsTable)->insert($data);
        return $this->db->insertID();
    }

    /**
     * 获取用户的所有工作流配置
     *
     * @param int $userId 用户ID
     * @param bool $enabledOnly 是否只返回启用的工作流
     * @return array 用户工作流配置数组
     */
    public function getUserWorkflows(int $userId, bool $enabledOnly = false): array
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where('user_id', $userId);

        if ($enabledOnly) {
            $builder->where('is_enabled', 1);
        }

        $query = $builder->get();
        $results = $query->getResultArray();

        // 解码JSON字段
        foreach ($results as &$row) {
            if (!empty($row['custom_config'])) {
                $row['custom_config'] = json_decode($row['custom_config'], true);
            }
        }

        return $results;
    }

    /**
     * 获取用户的特定工作流配置
     *
     * @param int $userId 用户ID
     * @param int $workflowId 工作流模板ID
     * @return array|null 用户工作流配置或null
     */
    public function getUserWorkflow(int $userId, int $workflowId): ?array
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where('user_id', $userId);
        $builder->where('workflow_id', $workflowId);
        $query = $builder->get();

        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // 解码JSON字段
        if (!empty($result['custom_config'])) {
            $result['custom_config'] = json_decode($result['custom_config'], true);
        }

        return $result;
    }

    /**
     * 通过ID获取用户工作流配置
     *
     * @param int $id 用户工作流ID
     * @return array|null 用户工作流配置或null
     */
    public function getUserWorkflowById(int $id): ?array
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where($this->userWorkflowPrimaryKey, $id);
        $query = $builder->get();

        $result = $query->getRowArray();

        if (!$result) {
            return null;
        }

        // 解码JSON字段
        if (!empty($result['custom_config'])) {
            $result['custom_config'] = json_decode($result['custom_config'], true);
        }

        return $result;
    }

    /**
     * 更新用户工作流配置
     *
     * @param int $userWorkflowId 用户工作流ID
     * @param array $data 更新数据
     * @return bool 是否成功
     */
    public function updateUserWorkflow(int $userWorkflowId, array $data): bool
    {
        // 准备可更新字段
        $updateData = [];
        if (isset($data['is_enabled'])) $updateData['is_enabled'] = (bool)$data['is_enabled'];

        // 处理JSON配置更新
        if (isset($data['custom_config']) && is_array($data['custom_config'])) {
            $updateData['custom_config'] = json_encode(
                $data['custom_config'],
                JSON_UNESCAPED_UNICODE
            );
        }

        // 如果没有数据更新则返回
        if (empty($updateData)) {
            return false;
        }

        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where($this->userWorkflowPrimaryKey, $userWorkflowId);
        return $builder->update($updateData);
    }

    /**
     * 更新用户工作流启用状态
     *
     * @param int $userId 用户ID
     * @param int $workflowId 工作流ID
     * @param bool $isEnabled 是否启用 (true=1, false=0)
     * @return bool 是否更新成功
     */
    public function updateUserWorkflowStatus(int $userId, int $workflowId, bool $isEnabled): bool
    {
        try {
            $builder = $this->db->table($this->userWorkflowsTable);

            // 更新条件：匹配用户ID和工作流ID
            $builder->where('user_id', $userId);
            $builder->where('workflow_id', $workflowId);

            // 更新数据
            $updateData = [
                'is_enabled' => $isEnabled ? 1 : 0,
            ];

            $result = $builder->update($updateData);

            // 检查是否实际更新了记录
            if ($this->db->affectedRows() === 0) {
                log_message('debug', "未找到匹配的用户工作流记录 - 用户ID: {$userId}, 工作流ID: {$workflowId}");
                return false;
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', "更新用户工作流状态失败 - 用户ID: {$userId}, 工作流ID: {$workflowId} - 错误: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 删除用户工作流配置
     *
     * @param int $userWorkflowId 用户工作流ID
     * @return bool 是否成功
     */
    public function deleteUserWorkflow(int $userWorkflowId): bool
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where($this->userWorkflowPrimaryKey, $userWorkflowId);
        return $builder->delete();
    }

    /**
     * 删除用户的所有工作流配置
     *
     * @param int $userId 用户ID
     * @return bool 是否成功
     */
    public function deleteAllUserWorkflows(int $userId): bool
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where('user_id', $userId);
        return $builder->delete();
    }

    /**
     * 检查工作流是否属于用户
     *
     * @param int $userWorkflowId 用户工作流ID
     * @param int $userId 用户ID
     * @return bool 是否属于该用户
     */
    public function isUserWorkflowOwner(int $userWorkflowId, int $userId): bool
    {
        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->select('1');
        $builder->where($this->userWorkflowPrimaryKey, $userWorkflowId);
        $builder->where('user_id', $userId);
        $query = $builder->get();

        return $query->getRow() !== null;
    }

    /**
     * 切换用户工作流的启用状态
     *
     * @param int $userWorkflowId 用户工作流ID
     * @return bool|null 新的启用状态或null（表示更新失败）
     */
    public function toggleUserWorkflowStatus(int $userWorkflowId): ?bool
    {
        // 获取当前状态
        $current = $this->getUserWorkflowById($userWorkflowId);
        if (!$current) {
            return null;
        }

        // 切换状态
        $newStatus = !$current['is_enabled'];

        $builder = $this->db->table($this->userWorkflowsTable);
        $builder->where($this->userWorkflowPrimaryKey, $userWorkflowId);
        $success = $builder->update(['is_enabled' => $newStatus]);

        return $success ? $newStatus : null;
    }

    /**
     * 获取用户工作流的完整信息（结合模板数据）
     *
     * @param int $userWorkflowId 用户工作流ID
     * @return array|null 完整的工作流信息或null
     */
    public function getFullUserWorkflowInfo(int $userWorkflowId): ?array
    {
        // 获取用户工作流配置
        $userWorkflow = $this->getUserWorkflowById($userWorkflowId);
        if (!$userWorkflow) {
            return null;
        }

        // 获取工作流模板
        $workflowTemplate = $this->getWorkflowById($userWorkflow['workflow_id']);
        if (!$workflowTemplate) {
            return null;
        }

        // 合并结果
        return [
            'user_workflow' => $userWorkflow,
            'workflow_template' => $workflowTemplate
        ];
    }

    /**
     * 更新用户工作流配置中的特定字段
     *
     * @param int $userWorkflowId 用户工作流ID
     * @param string $key 配置键名
     * @param mixed $value 配置值
     * @return bool 是否成功
     */
    public function updateUserWorkflowConfigValue(int $userWorkflowId, string $key, $value): bool
    {
        // 获取当前配置
        $userWorkflow = $this->getUserWorkflowById($userWorkflowId);
        if (!$userWorkflow) {
            return false;
        }

        // 更新配置值
        $customConfig = $userWorkflow['custom_config'] ?? [];
        $customConfig[$key] = $value;

        // 保存更新
        return $this->updateUserWorkflow($userWorkflowId, [
            'custom_config' => $customConfig
        ]);
    }
}
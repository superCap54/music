<?php
namespace Core\Musicdata\Models;
use CodeIgniter\Model;

class DistrokidModel extends Model
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getLicensedMusic($user_id)
    {
        $builder = $this->db->table('sp_user_music_licenses as uml');
        $builder->select('ml.*, uml.expiry_date as license_expiry_date');
        $builder->join('sp_music_library as ml', 'ml.id = uml.music_id');
        $builder->where('uml.user_id', $user_id);
        $builder->where('uml.expiry_date >', time());
        return $builder->get()->getResultArray();
    }

    public function getDashboardData($licensedIsrcs, $reporting_date = null)
    {
        $builder = $this->db->table('sp_music_royalties')
            ->select([
                'SUM(quantity) as total_views',
                'SUM(earnings_usd) as total_earnings',
                'COUNT(DISTINCT country) as countries_reached'
            ])
            ->whereIn('isrc', $licensedIsrcs)
            ->whereIn('store', ['YouTube (Ads)', 'YouTube (ContentID)', 'YouTube (Red)']);

        if ($reporting_date) {
            $builder->where('reporting_date', $reporting_date);
        }

        return $builder->get()->getRowArray();
    }

    public function getMonthlyData($licensedIsrcs, $reporting_date = null)
    {
        $builder = $this->db->table('sp_music_royalties')
            ->select([
                'sale_month as month',
                'SUM(quantity) as views',
                'SUM(earnings_usd) as earnings'
            ])
            ->whereIn('isrc', $licensedIsrcs)
            ->whereIn('store', ['YouTube (Ads)', 'YouTube (ContentID)', 'YouTube (Red)']);

        if ($reporting_date) {
            $builder->where('reporting_date', $reporting_date);
        }

        return $builder->groupBy('reporting_date')
            ->orderBy('reporting_date', 'DESC')
            ->limit(12)
            ->get()
            ->getResultArray();
    }

    public function getSongPerformanceData($licensedIsrcs, $report_month = null)
    {
        $builder = $this->db->table('sp_music_royalties a');
        $builder->select([
            'a.isrc',
            'a.title',
            'a.sale_month',
            'SUM(a.quantity) AS total_plays',
            'SUM(a.earnings_usd) AS earnings',
            '(SELECT b.country 
              FROM sp_music_royalties b 
              WHERE b.isrc = a.isrc 
                AND b.sale_month = a.sale_month
              GROUP BY b.country
              ORDER BY SUM(b.earnings_usd) DESC
              LIMIT 1) AS top_country'
        ]);
        $builder->whereIn('a.isrc', $licensedIsrcs);
        $builder->whereIn('a.store', ['YouTube (Ads)', 'YouTube (ContentID)', 'YouTube (Red)']);

        if ($report_month) {
            $builder->where('a.reporting_date', $report_month);
        }

        return $builder->groupBy('a.isrc', 'a.sale_month', 'a.title')
            ->orderBy('a.reporting_date', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getCountryEarnings($licensedIsrcs, $report_month = null)
    {
        $builder = $this->db->table('sp_music_royalties')
            ->select([
                'country',
                'SUM(quantity) as total_views',
                'SUM(earnings_usd) as total_earnings'
            ])
            ->whereIn('isrc', $licensedIsrcs)
            ->whereIn('store', ['YouTube (Ads)', 'YouTube (ContentID)', 'YouTube (Red)']);

        if ($report_month) {
            $builder->where('reporting_date', $report_month);
        }

        return $builder->groupBy('country')
            ->orderBy('total_earnings', 'DESC')
            ->get()
            ->getResultArray();
    }
}
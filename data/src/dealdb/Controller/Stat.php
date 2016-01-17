<?php
namespace dealdb\Controller;

class Stat extends Base
{
    /**
     * Show statistics
     */
    public function indexAction()
    {
        $redis = $this->getRedis();
        $stat = $redis->hGetAll('stat');
        $balance = $redis->hGetAll('balance');
        $qiwi = $redis->get('qiwi');

        $this->render('stat/index', [
            'stat' => $stat,
            'qiwi' => $qiwi,
            'balance' => $balance
        ]);
    }

    public function clearQiwiAction()
    {
        $redis = $this->getRedis();
        $redis->del('qiwi');

        $this->flashMessage('Qiwi refreshed');
        $this->redirect('/');
    }
}
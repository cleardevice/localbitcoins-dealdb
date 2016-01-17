<?php
namespace dealdb\Controller;

class Deal extends Base
{
    /**
     * Add new dial
     * @method get
     */
    public function addAction()
    {
        $this->render('deal/add');
    }

    /**
     * Adding data
     * @method post
     */
    public function addDealAction()
    {
        $paramsList = [
            'value',
            'currency',
            'provider',
            'price',
            'commission'
        ];

        $mandatory = [
            'value',
            'currency',
            'provider',
            'price'
        ];

        $deal = $this->getParams($paramsList, $mandatory, 'post');

        if (false === is_numeric($deal['value'])) {
            $this->persistFormData();
            $this->flashError('Value is not numeric');
            $this->redirect('/add');
        }

        if (false === is_numeric($deal['price'])) {
            $this->persistFormData();
            $this->flashError('Price is not numeric');
            $this->redirect('/add');
        }

        $deal['btc'] = round(abs($deal['value']) / $deal['price'], 6);
        if (false === empty($deal['commission'])) {
            $deal['commission'] = $deal['btc'] / 100;
        } else {
            $deal['commission'] = 0;
        }

        if ($deal['value'] > 0) {
            $deal['btc'] *= -1;
        }
        $deal['btc'] = $deal['btc'] - round($deal['commission'], 6);

        $deal['date'] = date('Y-m-d');
        $deal['time'] = date('H:i:s');

        $redis = $this->getRedis();

        $id = $redis->incr('id');
        $redis->hMset('deal:' . $id, $deal);

        # RUB limit
        if ($deal['provider'] === 'QIWI' && $deal['currency'] === 'RUB' && $deal['value'] > 0) {
            $redis->incrByFloat('qiwi', $deal['value']);
        }

        # balance
        $redis->hIncrByFloat('balance', 'BTC', $deal['btc']);
        $redis->hIncrByFloat('balance', $deal['currency'], round($deal['value'], 2));

        $this->flashMessage('Dial added');

        $this->redirect('/add');
    }

    /**
     * Show all deals
     */
    public function allAction()
    {
        date_default_timezone_set(ini_get('date.timezone'));

        $redis = $this->getRedis();
        $id = $redis->get('id');

        $deals = [];
        $stat = [
            'RUB' => 0,
            'UAH' => 0,
            'USD' => 0,
            'BTC' => 0,
            'COMMISSION' => 0,
            'RUB+' => 0
        ];
        for ($i = 1; $i <= $id; $i++) {
            $deal = $redis->hGetALl('deal:'.$i);

            # datetime
            $time = strtotime(sprintf('%s %s UTC', $deal['date'], $deal['time']));
            $deal['date'] = date('Y-m-d', $time);
            $deal['time'] = date('H:i:s', $time);

            $deals[] = $deal;

            # stats
            $stat[$deal['currency']] += $deal['value'];
            $stat['BTC'] += $deal['btc'];
            $stat['COMMISSION'] += $deal['commission'];
        }

        $this->redis->hMset('stat', $stat);

        $this->render('deal/all', [
            'deals' => $deals,
            'stat' => $stat
        ]);
    }

    /**
     * Export to csv
     */
    public function exportAction()
    {
        date_default_timezone_set(ini_get('date.timezone'));

        $redis = $this->getRedis();
        $id = $redis->get('id');

        $fields = [
            'id', 'Date', 'Time', 'Fiat, RUB', 'Fiat, UAH', 'Fiat, USD', 'Provider', 'Price', 'Value, BTC', 'Comm BTC'
        ];

        header('Cache-Control: must-revalidate');
        header('Pragma: must-revalidate');

        header('Content-type: text/csv');
        header(sprintf('Content-disposition: attachment; filename=%s.csv', date('Ymd_His')));

        $out = fopen('php://output', 'w');
        fputcsv($out, $fields);
        for ($i = 1; $i <= $id; $i++) {
            $deal = $redis->hGetALl('deal:' . $i);

            # datetime
            $time = strtotime(sprintf('%s %s UTC', $deal['date'], $deal['time']));
            $deal['date'] = date('Y-m-d', $time);
            $deal['time'] = date('H:i:s', $time);

            $csv_data = [
                $i,
                $deal['date'],
                $deal['time'],
                ($deal['currency'] === 'RUB') ? $deal['value'] : 0,
                ($deal['currency'] === 'UAH') ? $deal['value'] : 0,
                ($deal['currency'] === 'USD') ? $deal['value'] : 0,
                $deal['provider'],
                $deal['btc'],
                $deal['commission']
            ];
            fputcsv($out, $csv_data);
        }
        fclose($out);
    }
}
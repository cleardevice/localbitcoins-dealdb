<?php
namespace dealdb\Controller;

use Redis;
use SlimController\SlimController;

class Base extends SlimController
{
    /**
     * @var Redis
     */
    protected $redis;

    protected function flashMessage($text)
    {
        $this->app->flash('message', $text);
    }

    protected function flashError($text)
    {
        $this->app->flash('error', $text);
    }

    protected function getParams($paramsList, $mandatoryParams, $method='get')
    {
        $params = $this->params($paramsList, $method);

        $emptyParams = [];
        foreach ($mandatoryParams as $param) {
            if (empty($params[$param])) {
                $emptyParams[] = $param;
            }
        }

        if (false === empty($emptyParams)) {
            $this->flashError('Empty fields: ' . implode(', ', $emptyParams));
            return false;
        }

        return $params;
    }

    /**
     * @return Redis
     */
    protected function getRedis()
    {
        if (false === empty($this->redis)) {
            return $this->redis;
        }

        $redis = new Redis();
        $redis->connect('redis');
        $this->redis = $redis;

        return $redis;
    }

    protected function persistFormData()
    {
        if (false === empty($_REQUEST['data'])) {
            $_SESSION['formData']['data'] = $_REQUEST['data'];
        }
    }

    /**
     * Renders output with given template
     *
     * @param string $template Name of the template to be rendererd
     * @param array $args Args for view
     */
    protected function render($template, $args = array())
    {
        if (isset($_SESSION['formData'])) {
            $args = array_merge($_SESSION['formData'], $args);

            unset($_SESSION['formData']);
        }

        parent::render($template, $args);
    }
}
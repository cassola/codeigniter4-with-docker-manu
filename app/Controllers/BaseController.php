<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    private const SUPPORTED_LOCALES = ['es', 'pt', 'en', 'it'];

    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['url'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->loadUserPreferencesFromDb();

        $locale = session('locale');
        if (is_string($locale) && in_array($locale, self::SUPPORTED_LOCALES, true)) {
            service('request')->setLocale($locale);
            service('language')->setLocale($locale);
        }
    }

    private function loadUserPreferencesFromDb(): void
    {
        $session = session();
        if ($session->get('prefs_loaded') === true) {
            return;
        }

        $db = db_connect();
        $userId = (int) ($session->get('current_user_id') ?? 0);
        if ($userId <= 0) {
            $user = $db->table('rm_user')->select('id')->orderBy('id')->get(1)->getRowArray();
            if ($user) {
                $userId = (int) $user['id'];
                $session->set('current_user_id', $userId);
            }
        }

        if ($userId > 0) {
            $userInfo = $db->table('rm_user')
                ->select('full_name, username')
                ->where('id', $userId)
                ->get()
                ->getRowArray();
            if ($userInfo) {
                $session->set('current_user_name', (string) $userInfo['full_name']);
                $session->set('current_username', (string) $userInfo['username']);
            }
        }

        if ($userId > 0 && $db->tableExists('rm_userpreference')) {
            $prefs = $db->table('rm_userpreference')->where('rm_user_id', $userId)->get()->getRowArray();
            if ($prefs) {
                $locale = (string) ($prefs['rm_default_locale'] ?? '');
                if ($locale !== '' && in_array($locale, self::SUPPORTED_LOCALES, true)) {
                    $session->set('locale', $locale);
                }
                $session->set('default_site_id', (int) ($prefs['rm_default_site_id'] ?? 0));
            }
        }

        $session->set('prefs_loaded', true);
    }
}

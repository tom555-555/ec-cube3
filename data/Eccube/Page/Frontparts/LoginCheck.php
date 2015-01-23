<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2014 LOCKON CO.,LTD. All Rights Reserved.
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Page\FrontParts;

use Eccube\Page\AbstractPage;
use Eccube\Common\Cookie;
use Eccube\Common\Customer;
use Eccube\Common\Display;
use Eccube\Common\FormParam;
use Eccube\Common\Query;
use Eccube\Common\Response;
use Eccube\Common\Helper\MobileHelper;
use Eccube\Common\Helper\PurchaseHelper;
use Eccube\Common\Util\Utils;

/**
 * ログインチェック のページクラス.
 *
 * TODO mypage/LC_Page_Mypage_LoginCheck と統合
 *
 * @package Page
 * @author LOCKON CO.,LTD.
 */
class LoginCheck extends AbstractPage
{
    /**
     * Page を初期化する.
     *
     * @return void
     */
    public function init()
    {
        $this->skip_load_page_layout = true;
        parent::init();
    }

    /**
     * Page のプロセス.
     *
     * @return void
     */
    public function process()
    {
        $this->action();
        $this->sendResponse();
    }

    /**
     * Page のアクション.
     *
     * @return void
     */
    public function action()
    {
        //決済処理中ステータスのロールバック
        $objPurchase = new PurchaseHelper();
        $objPurchase->cancelPendingOrder(PENDING_ORDER_CANCEL_FLAG);

        // 会員管理クラス
        $objCustomer = new Customer();
        // クッキー管理クラス
        $objCookie = new Cookie();
        // パラメーター管理クラス
        $objFormParam = new FormParam();

        // パラメーター情報の初期化
        $this->lfInitParam($objFormParam);

        // リクエスト値をフォームにセット
        $objFormParam->setParam($_POST);

        $url = htmlspecialchars($_POST['url'], ENT_QUOTES);

        // モードによって分岐
        switch ($this->getMode()) {
            case 'login':
                // --- ログイン

                // 入力値のエラーチェック
                $objFormParam->trimParam();
                $objFormParam->toLower('login_email');
                $arrErr = $objFormParam->checkError();

                // エラーの場合はエラー画面に遷移
                if (count($arrErr) > 0) {
                    if (Display::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
                        echo $this->lfGetErrorMessage(TEMP_LOGIN_ERROR);
                        Response::actionExit();
                    } else {
                        Utils::sfDispSiteError(TEMP_LOGIN_ERROR);
                        Response::actionExit();
                    }
                }

                // 入力チェック後の値を取得
                $arrForm = $objFormParam->getHashArray();

                // クッキー保存判定
                if ($arrForm['login_memory'] == '1' && $arrForm['login_email'] != '') {
                    $objCookie->setCookie('login_email', $arrForm['login_email']);
                } else {
                    $objCookie->setCookie('login_email', '');
                }

                // 遷移先の制御
                if (count($arrErr) == 0) {
                    // ログイン処理
                    if ($objCustomer->doLogin($arrForm['login_email'], $arrForm['login_pass'])) {
                        if (Display::detectDevice() === DEVICE_TYPE_MOBILE) {
                            // ログインが成功した場合は携帯端末IDを保存する。
                            $objCustomer->updateMobilePhoneId();

                            /*
                             * email がモバイルドメインでは無く,
                             * 携帯メールアドレスが登録されていない場合
                             */
                            $objMobile = new MobileHelper();
                            if (!$objMobile->gfIsMobileMailAddress($objCustomer->getValue('email'))) {
                                if (!$objCustomer->hasValue('email_mobile')) {
                                    Response::sendRedirectFromUrlPath('entry/email_mobile.php');
                                    Response::actionExit();
                                }
                            }
                        }

                        // --- ログインに成功した場合
                        if (Display::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
                            echo Utils::jsonEncode(array('success' => $url));
                        } else {
                            Response::sendRedirect($url);
                        }
                        Response::actionExit();
                    } else {
                        // --- ログインに失敗した場合

                        // ブルートフォースアタック対策
                        // ログイン失敗時に遅延させる
                        sleep(LOGIN_RETRY_INTERVAL);

                        $arrForm['login_email'] = strtolower($arrForm['login_email']);
                        $objQuery = Query::getSingletonInstance();
                        $where = '(email = ? OR email_mobile = ?) AND status = 1 AND del_flg = 0';
                        $exists = $objQuery->exists('dtb_customer', $where, array($arrForm['login_email'], $arrForm['login_email']));
                        // ログインエラー表示 TODO リファクタリング
                        if ($exists) {
                            if (Display::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
                                echo $this->lfGetErrorMessage(TEMP_LOGIN_ERROR);
                                Response::actionExit();
                            } else {
                                Utils::sfDispSiteError(TEMP_LOGIN_ERROR);
                                Response::actionExit();
                            }
                        } else {
                            if (Display::detectDevice() === DEVICE_TYPE_SMARTPHONE) {
                                echo $this->lfGetErrorMessage(SITE_LOGIN_ERROR);
                                Response::actionExit();
                            } else {
                                Utils::sfDispSiteError(SITE_LOGIN_ERROR);
                                Response::actionExit();
                            }
                        }
                    }
                } else {
                    // XXX 到達しない？
                    // 入力エラーの場合、元のアドレスに戻す。
                    Response::sendRedirect($url);
                    Response::actionExit();
                }

                break;
            case 'logout':
                // --- ログアウト

                // ログイン情報の解放
                $objCustomer->EndSession();
                // 画面遷移の制御
                $mypage_url_search = strpos('.'.$url, 'mypage');
                if ($mypage_url_search == 2) {
                    // マイページログイン中はログイン画面へ移行
                    Response::sendRedirectFromUrlPath('mypage/login.php');
                } else {
                    // 上記以外の場合、トップへ遷移
                    Response::sendRedirect(TOP_URL);
                }
                Response::actionExit();

                break;
            default:
                break;
        }

    }

    /**
     * パラメーター情報の初期化.
     *
     * @param  FormParam $objFormParam パラメーター管理クラス
     * @return void
     */
    public function lfInitParam(&$objFormParam)
    {
        $objFormParam->addParam('記憶する', 'login_memory', INT_LEN, 'n', array('MAX_LENGTH_CHECK', 'NUM_CHECK'));
        $objFormParam->addParam('メールアドレス', 'login_email', MTEXT_LEN, 'a', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'));
        $objFormParam->addParam('パスワード', 'login_pass', PASSWORD_MAX_LEN, '', array('EXIST_CHECK', 'MAX_LENGTH_CHECK'));
    }

    /**
     * エラーメッセージを JSON 形式で返す.
     *
     * TODO リファクタリング
     * この関数は主にスマートフォンで使用します.
     *
     * @param integer エラーコード
     * @return string JSON 形式のエラーメッセージ
     * @see LC_PageError
     */
    public function lfGetErrorMessage($error)
    {
        switch ($error) {
            case TEMP_LOGIN_ERROR:
                $msg = "メールアドレスもしくはパスワードが正しくありません。\n本登録がお済みでない場合は、仮登録メールに記載されているURLより本登録を行ってください。";
                break;
            case SITE_LOGIN_ERROR:
            default:
                $msg = 'メールアドレスもしくはパスワードが正しくありません。';
        }

        return Utils::jsonEncode(array('login_error' => $msg));
    }
}
<?php
include 'inc/functions2.php';
$identity = getIdentity();
$tor = checkDNSBL($identity);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $captcha = generate_captcha($config['captcha']['extra']);

        $html = "{$captcha['html']}<br/>
                <input class='captcha_text' name='captcha_text' size='25' maxlength='6' autocomplete='off' type='text' autocorrect='off' autocapitalize='off' spellcheck='false'>
                <input class='captcha_cookie' name='captcha_cookie' type='hidden' autocomplete='off' value='{$captcha['cookie']}'><br/>";

        $body = Element("8chan/dnsbls_pop.html", array("config" => $config, "ayah_html" => $html));

        syslog(LOG_NOTICE, "{CAPTCHA}\tGET\t{$_SERVER['REMOTE_ADDR']}\tdnsbls_bypass.php\t{$config['captcha']['extra']}\t{$captcha['cookie']}\t{$captcha['answer']}");

        echo $body;
} else {
        $resp = file_get_contents($config['captcha']['provider_check_pop'] . "?" . http_build_query([
                'mode' => 'check',
                'entry' => 'dnsbls',
                'text' => $_POST['captcha_text'],
                'extra' => $config['captcha']['extra'],
                'cookie' => $_POST['captcha_cookie']
        ]));

        if ($resp === '1') {
                if (!$tor) {
                        $query = prepare('REPLACE INTO ``dnsbl_bypass`` (ip, created, uses) VALUES(:ip, NOW(), 0)');
                        $query->bindValue(':ip', $identity);
                        $query->execute() or error(db_error($query));
                }
                $cookie = bin2hex(openssl_random_pseudo_bytes(16));
                $query = prepare('INSERT INTO ``tor_cookies`` VALUES(:cookie, NOW(), 0)');
                $query->bindValue(':cookie', $cookie);
                $query->execute() or error(db_error($query));
                setcookie("tor", $cookie, time()+60*60*3);
                $message = '<h1>'._('Success!').'</h1><div class=\"subtitle\">'._('You may now go back and make your post.').'</div>';
                echo '{"status":'.$resp.',"message":"'.$message.'"}';exit;
        } else {
                $new_captcha = "";
                if ($tor) {
                        $message = '<h1>'._('You failed the CAPTCHA').'</h1>. <div class=\"subtitle\"><a href=\"http://oxwugzccvk3dk6tj.onion/dnsbls_bypass.php\" target=\"_blank\">'._('Try again.').'</a>'._(' If it\'s not working, email dmca@8ch.net for support.').'</div>';
                } else {
                        $captcha = generate_captcha($config['captcha']['extra']);

                        $new_captcha = addslashes($captcha['html']).'<br/><input class=\"captcha_text\" name=\"captcha_text\" size=\"25\" maxlength=\"6\" autocomplete=\"off\" type=\"text\" autocorrect=\"off\" autocapitalize=\"off\" spellcheck=\"false\"><input class=\"captcha_cookie\" name=\"captcha_cookie\" type=\"hidden\" autocomplete=\"off\" value=\"'.$captcha['cookie'].'\"><br/>';

                        $message = '<h1>'._('You failed the CAPTCHA').'</h1><p><div class=\"subtitle\">'._('Try again. If it\'s not working, email dmca@8ch.net for support. Or <br><a href=\"http://8ch.net/dnsbls_bypass.php\" target=\"_blank\">')._('Click here if captcha doesnt load').'</a></div></p>';

                }

                echo '{"status":'.$resp.',"message":"'.$message.'","new_captcha":"'.$new_captcha.'"}';
        }
}

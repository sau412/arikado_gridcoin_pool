<?php
// Language file

$message_logout_success="Logout successfull";
$message_login_error="Incorrect login/password";
$message_xml_error="XML parsing error";

$message_login_fail="Incorrect username or password";

$message_register_fail_login="Register fail. Username validation failed.";
$message_register_fail_password="Register fail. Password validation failed.";
$message_register_fail_password_mismatch="Register fail. Password mismatch.";
$message_register_fail_email="Register fail. E-mail format validation failed.";
$message_register_fail_payout_currency="Register fail. Payout currency validation failed.";
$message_register_fail_payout_address="Register fail. Payout address validation failed.";
$message_register_fail_db="Register fail. DB error, contact admin.";
$message_register_fail_username_exists="Register fail. Username exists.";

$message_register_recaptcha_error="Register fail. Invalid reCAPTCHA.";
$message_login_recaptcha_error="Login fail. Invalid reCAPTCHA.";

$message_register_success="Registration complete. Enter your login and password to login.";

$message_change_settings_ok="Settings changed";
$message_change_settings_password_fail="Change settings fail. Password incorrect";
$message_change_settings_validation_fail="Change settings fail. Data is not valid";

$message_project_attached="Project attached";
$message_project_detached="Project detached";
$message_project_status_changed="Project status changed";
$message_project_settings_changed="Project settings changed";

$message_message_sent="Message sent";

$message_faucet_sent="Claim succesful";

$message_host_deleted="Host deleted";

$message_user_status_changed="User status changed";

$message_billing_ok="Billing ok";

$message_bad_token="Bad token";

$message_host_error="Host already belongs to another user";

$message_pool_info_changed="Pool info changed";
$message_pool_txid_set="TX ID is set";

$lang_data_json=<<<_END
{
"news_variable":{
        "en":"pool_info",
        "ru":"pool_info_ru",
        "de":"pool_info_de",
        "zh":"pool_info_zh"
        },
"message_logout_success":{
        "en":"Logout successfull",
        "ru":"Вы разлогинились",
        "de":"Abmelden erfolgreich",
        "zh":"成功退出"
        },
"txid_limit_not_reached":{
        "en":"limit not reached",
        "ru":"порог вывода не достигнут",
        "de":"Limit nicht erreicht",
        "zh":"没有到达门限"
        },
"menu_pool_info":{
        "en":"Pool info",
        "ru":"Информация",
        "de":"Pool info",
        "zh":"矿池信息"
        },
"menu_login":{
        "en":"Login",
        "ru":"Войти",
        "de":"Anmelden",
        "zh":"登陆"
        },
"menu_register":{
        "en":"Register",
        "ru":"Зарегистрироваться",
        "de":"Registrieren",
        "zh":"注册"
        },
"menu_payouts":{
        "en":"Payouts",
        "ru":"Выплаты",
        "de":"Auszahlungen",
        "zh":"支付"
        },
"menu_rating_by_host_mag":{
        "en":"Ranking by host mag",
        "ru":"Рейтинг хостов",
        "de":"Rangliste nach Host mag",
        "zh":"Host mag排名"
        },
"menu_rating_by_user_mag":{
        "en":"Ranking by user mag",
        "ru":"Рейтинг пользователей",
        "de":"Rangliste nach Nutzer mag",
        "zh":"用户mag排名"
        },
"menu_rating_by_host_project_mag":{
        "en":"Ranking by host project mag",
        "ru":"Рейтинг хостов по проектам",
        "de":"Rangliste nach Projekt Host mag",
        "zh":"主机项目mag排名"
        },
"menu_rating_by_user_project_mag":{
        "en":"Ranking by user project mag",
        "ru":"Рейтинг пользователей по проектам",
        "de":"Rangliste nach Projekt Nutzer mag",
        "zh":"用户项目mag排名"
        },
"menu_pool_stats":{
        "en":"Pool project stats",
        "ru":"Статистика по проектам",
        "de":"Pool Projekt Statistiken",
        "zh":"矿池项目统计"
        },
"menu_statistics":{
        "en":"Statistics",
        "ru":"Рейтинги",
        "de":"Statistiken",
        "zh":"统计"
        },
"menu_currencies":{
        "en":"Currencies",
        "ru":"Валюты",
        "de":"Währungen",
        "zh":"货币"
        },
"menu_block_explorer":{
        "en":"Block explorer",
        "ru":"Обозреватель блоков",
        "de":"Block explorer",
        "zh":"区块浏览器"
        },
"menu_info":{
        "en":"Info",
        "ru":"Данные",
        "de":"Info",
        "zh":"信息"
        },
"menu_feedback":{
        "en":"Feedback",
        "ru":"Обратная связь",
        "de":"Feedback",
        "zh":"Feedback"
        },
"menu_settings":{
        "en":"Settings",
        "ru":"Настройки",
        "de":"Einstellungen",
        "zh":"设置"
        },
"menu_your_hosts":{
        "en":"Your hosts",
        "ru":"Ваши хосты",
        "de":"Deine Hosts",
        "zh":"您的主机"
        },
"menu_boinc_results_by_host":{
        "en":"Results by host",
        "ru":"Результаты по хостам",
        "de":"Ergebnisse pro Host",
        "zh":"主机bonic结果"
        },
"menu_boinc_results_by_project":{
        "en":"Results by project",
        "ru":"Результаты по проектам",
        "de":"Ergebnisse pro Projekt",
        "zh":"项目bonic结果"
        },
"menu_boinc_results_by_user":{
        "en":"Results by user",
        "ru":"Результаты по пользователю",
        "de":"Ergebnisse pro Nutzer",
        "zh":"用户bonic结果"
        },
"menu_boinc_results_all_valuable":{
        "en":"All relevant results",
        "ru":"Все значимые результаты",
        "de":"Alle relevanten Ergebnisse",
        "zh":"所有相关结果"
        },
"menu_boinc_results_all":{
        "en":"All results",
        "ru":"Все результаты",
        "de":"Alle Ergebnisse",
        "zh":"所有结果"
        },
"menu_boinc":{
        "en":"BOINC results",
        "ru":"Результаты BOINC",
        "de":"BOINC Ergebnisse",
        "zh":"BOINC结果"
        },
"menu_faucet":{
        "en":"Faucet",
        "ru":"Кран",
        "de":"Faucet",
        "zh":"Faucet"
        },
"menu_user_control":{
        "en":"User control",
        "ru":"Управление пользователями",
        "de":"Benutzerverwaltung",
        "zh":"用户控制"
        },
"menu_project_control":{
        "en":"Project control",
        "ru":"Управление проектами",
        "de":"Projektverwaltung",
        "zh":"项目控制"
        },
"menu_billing":{
        "en":"Billing",
        "ru":"Биллинг",
        "de":"Abrechnungen",
        "zh":"账单"
        },
"menu_pool_info_editor":{
        "en":"Pool info editor",
        "ru":"Редактор новостей",
        "de":"Pool info Editor",
        "zh":"矿池信息编辑"
        },
"menu_log":{
        "en":"View log",
        "ru":"Просмотр журнала",
        "de":"Potokollanzeige",
        "zh":"log视图"
        },
"menu_messages_view":{
        "en":"View messages",
        "ru":"Просмотр обратной связи",
        "de":"Feedback anzeigen",
        "zh":"消息视图"
        },
"menu_email_view":{
        "en":"View emails",
        "ru":"Просмотр почты",
        "de":"E-Mails anzeigen",
        "zh":"emails视图"
        },
"menu_control":{
        "en":"Control",
        "ru":"Управление",
        "de":"Verwaltung",
        "zh":"控制"
        },
"greeting_message":{
        "en":"Welcome",
        "ru":"Вы зашли как",
        "de":"Willkommmen",
        "zh":"欢迎"
        },
"register_header":{
        "en":"Register",
        "ru":"Регистрация",
        "de":"Registrierung",
        "zh":"注册"
        },
"register_username":{
        "en":"Username",
        "ru":"Имя пользователя",
        "de":"Nutzername",
        "zh":"用户名"
        },
"register_password":{
        "en":"Password",
        "ru":"Пароль",
        "de":"Passwort",
        "zh":"密码"
        },
"register_username_after":{
        "en":"required, only letters A-Z, a-z, numbers, dot, dash, underscore",
        "ru":"обязательное поле, допустимы латинские буквы, цифры, точка, подчёркивание, дефис",
        "de":"Pflichtfeld, nur Buchstaben A-Z, a-z, Zahlen, Punkt, Unterstrich, Bindestrich",
        "zh":"仅可以是字母A-Z，a-z，数字，点，横线，下划线"
        },
"register_password_after":{
        "en":"required at least $pool_min_password_length characters",
        "ru":"необходимо как минимум $pool_min_password_length символов",
        "de":"Mindestens $pool_min_password_length Zeichen",
        "zh":"至少需要 $pool_min_password_length Zeichen"
        },
"register_retype_password":{
        "en":"Re-type password",
        "ru":"Пароль ещё раз",
        "de":"Passwort wiederholen",
        "zh":"重新输入密码"
        },
"register_email":{
        "en":"E-mail",
        "ru":"Почта",
        "de":"E-Mail",
        "zh":"E-mail"
        },
"register_email_after":{
        "en":"for password recovery (you can write me from that mail, and I send you new password for account)",
        "ru":"для восстановления пароля (вы можете написать мне с этой почты и я отправлю вам новый пароль для аккаунта)",
        "de":"Zur Passwortwiederherstellung (du kannst mir von dieser Adresse aus eine E-Mail schreiben und ich werde dir dann ein neues Passwort für dein Konto schicken",
        "zh":"密码恢复（您可以给我发email，我将给您发送账号的新密码）"
        },
"register_payout_address":{
        "en":"Payout address",
        "ru":"Адрес для выплат",
        "de":"Auszahlungsadresse",
        "zh":"支付地址"
        },
"register_payout_currency":{
        "en":"payout currency",
        "ru":"валюта выплат",
        "de":"Auszahlungswährung",
        "zh":"支付货币"
        },
"register_payout_currency_after":{
        "en":"both required",
        "ru":"оба обязательны",
        "de":"Beide notwendig",
        "zh":"都需要"
        },
"register_submit":{
        "en":"Register",
        "ru":"Зарегистрироваться",
        "de":"Jetzt registrieren",
        "zh":"注册"
        },
"login_header":{
        "en":"Login",
        "ru":"Вход",
        "de":"Anmelden",
        "zh":"登陆"
        },
"login_username":{
        "en":"Username",
        "ru":"Имя пользователя",
        "de":"Nutzername",
        "zh":"用户名"
        },
"login_password":{
        "en":"Password",
        "ru":"Пароль",
        "de":"Passwort",
        "zh":"密码"
        },
"login_submit":{
        "en":"Login",
        "ru":"Войти",
        "de":"Anmelden",
        "zh":"登陆"
        },
"settings_header":{
        "en":"Settings",
        "ru":"Настройки",
        "de":"Einstellungen",
        "zh":"设置"
        },
"settings_desc":{
        "en":"GRC payouts are instant, alternative currencies payouts are cumulative and manual. It takes 1-2 days when payout limit reached to send payout (because manual mode now).",
        "ru":"Выплаты в GRC производятся сразу, выплаты в альтернативных валютах производятся вручную (пока) в течение 1-2 дней по достижению порога выплаты.",
        "de":"Zahlungen in GRC erfolgen sofort, Zahlungen in alternativen Währungen vorerst manuell innerhalb von 1-2 Tagen nach Erreichen der Zahlungsgrenze.",
        "zh":"GRC是即时支付，其他货币支付是货币累积至门限后，由人工进行支付，花费时间是1-2天（因为目前是人工模式）"
        },
"settings_note":{
        "en":"Changing alternative (non-GRC) currency or address notice: please note, owed amount linked to address, not to user. If you change address your previous address owed amount will not lost, but won't payed out until payout limit for previous address reached. You can contact admin for manual payout or change address back and receive payout when payout limit reached.",
        "ru":"Предупреждение о смене типа валюты на альтернативный: пожалуйста учтите, что долги пула привязаны к адресу, а не к пользователю. Если вы измените адрес, то сумма не потеряется, но и не будет выплачена пока не достигнут порог выплаты. Но вы всегда можете связаться с администратором ресурса для выплаты, или изменить адрес на старый и достигнуть порога выплаты таким образом.",
        "de":"Warnung vor alternativen Währungsänderungen: Bitte beachten Sie, dass die Poolschulden an die Adresse und nicht an den Benutzer gebunden sind. Wenn Sie Ihre Adresse ändern, geht der Betrag nicht verloren, aber er wird erst bei Erreichen der Zahlungsgrenze ausgezahlt. Sie können sich aber immer an den Administrator wenden, um die Zahlung vorzunehmen, oder die Adresse auf die alte Adresse ändern und so die Zahlungsschwelle erreichen.",
        "zh":"更改其他非GRC货币或地址请注意：已拥有的货币量和地址绑定，而不是和用户绑定。如果您更改您之前的地址，已拥有的货币量不会丢失，但是这部分货币不会被发送到之前的地址，直到达到支付门限为止。您可以联系管理员进行人工支付，或者改回地址，在达到门限后收取支付"
        },
"settings_email":{
        "en":"E-mail",
        "ru":"Почта",
        "de":"E-Mail",
        "zh":"E-Mail"
        },
"settings_email_reports":{
        "en":"send reports to email if task errors found",
        "ru":"отправлять оповещения об ошибках в заданиях",
        "de":"Benachrichtigungen über Berechnungsfehler per E-Mail senden",
        "zh":"如果发现错误请email发送报告"
        },
"settings_payout_address":{
        "en":"Payout address",
        "ru":"Адрес для выплат",
        "de":"Auszahlungsadresse",
        "zh":"支付地址"
        },
"settings_payout_currency":{
        "en":"currency",
        "ru":"валюта",
        "de":"Währung",
        "zh":"支付货币"
        },
"settings_payout_currency_after":{
        "en":"(look notice above)",
        "ru":"(смотри сообщение выше)",
        "de":"(siehe den obigen Hinweis)",
        "zh":"查看上述注意事项"
        },
"settings_password":{
        "en":"Password",
        "ru":"Пароль",
        "de":"Passwort",
        "zh":"密码"
        },
"settings_password_after":{
        "en":"the password is required to change settings",
        "ru":"необходим для применения изменений",
        "de":"Das Passwort wird benötgt um die Einstellungen zu ändern",
        "zh":"修改设置需要密码"
        },
"settings_new_password1":{
        "en":"New password",
        "ru":"Новый пароль",
        "de":"Neues Passwort",
        "zh":"新密码"
        },
"settings_new_password1_after":{
        "en":"only if you wish to change password",
        "ru":"только если вы хотите сменить пароль",
        "de":"Nur wenn du dein Passwort ändern willst",
        "zh":"仅当您希望修改密码"
        },
"settings_new_password2":{
        "en":"Re-type new password",
        "ru":"Новый пароль ещё раз",
        "de":"Neues Passwort wiederholen",
        "zh":"重新输入新密码"
        },
"settings_submit":{
        "en":"Update",
        "ru":"Применить",
        "de":"Anwenden",
        "zh":"更新"
        },
"user_hosts_header":{
        "en":"Your hosts",
        "ru":"Ваши хосты",
        "de":"Deine Hosts",
        "zh":"您的主机"
        },
"user_hosts_desc":{
        "en":"That information will be synced to your BOINC client. When attaching new project sync second time after 1-2 minutes to avoid incomplete sync. If you sync correctly, then you see your host in BOINC results after 1-3 hours.",
        "ru":"Эта информация будет синхронизирована с вашим BOINC клиентом. Когда вы подключетеле новый проект синхронизируйтесь дважды, второй раз через 1-2 минуты после первого (во избежание неполной синхронизации). Если синхронизация прошла успешно, вы увидите свои результаты во вкладке статистики BOINC",
        "de":"Diese Informationen werden mit Ihrem BOINC-Client synchronisiert. Wenn Sie ein neues Projekt verbinden, synchronisieren Sie zweimal, ein zweites Mal 1-2 Minuten nach dem ersten (um eine unvollständige Synchronisation zu vermeiden). Wenn die Synchronisation erfolgreich war, sehen Sie Ihren Host nach 1-3 Stunden in der Registerkarte BOINC Ergebnisse.",
        "zh":"信息将会和您的BOINC客户端同步。当连接上新项目时，您可以同步两次后1~2分钟，避免没有完全同步。如果您正确地同步，1~3小时后您将在主机中看到BOINC结果"
        },
"user_hosts_table_header":{
        "en":{"1":"Host info","2":"Projects"},
        "ru":{"1":"Хост","2":"Проекты"},
        "de":{"1":"Host info","2":"Projekte"},
        "zh":{"1":"主机信息","2":"项目"}
        },
"boinc_results_by_host_header":{
        "en":"BOINC results by host",
        "ru":"Результаты BOINC по хостам",
        "de":"BOINC Ergebnisse pro Host",
        "zh":"主机的BOINC结果"
        },
"boinc_results_by_host_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC",
        "de":"Diese Informationen stammen von verschiedenen BOINC Projekten",
        "zh":"各BOINC项目接收的信息"
        },
"boinc_results_by_host_table_header_user":{
        "en":{"1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Название","2":"CPU","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"1":"Host Name","2":"CPU","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"boinc_results_by_host_table_header_admin":{
        "en":{"0":"Username","1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"0":"Пользователь","1":"Название","2":"CPU","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"0":"Nutzername","1":"Host Name","2":"CPU","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"0":"Username","1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"boinc_results_by_project_header":{
        "en":"BOINC results by project",
        "ru":"Результаты BOINC по проектам",
        "de":"BOINC Ergebnisse pro Projekt",
        "zh":"项目的BOINC结果"
        },
"boinc_results_by_project_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC",
        "de":"Diese Informationen stammen von verschiedenen BOINC Projekten",
        "zh":"各BOINC项目接收的信息"
        },
"boinc_results_by_project_table_header":{
        "en":{"1":"Project","2":"&Sigma; RAC","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Проект","2":"&Sigma; RAC","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"1":"Projekt","2":"&Sigma; RAC","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"1":"Project","2":"&Sigma; RAC","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"boinc_results_by_user_header":{
        "en":"BOINC results by pool user",
        "ru":"Результаты BOINC по пользователям пула",
        "de":"BOINC Ergebnisse per Pool Nutzer",
        "zh":"矿池用户的BOINC结果"
        },
"boinc_results_by_user_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC",
        "de":"Diese Informationen stammen von verschiedenen BOINC Projekten",
        "zh":"各BOINC项目接收的信息"
        },
"boinc_results_by_user_table_header":{
        "en":{"1":"Username","2":"Task stats","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Пользователь","2":"Задания","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"1":"Nutzername","2":"Aufgaben","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"1":"Username","2":"Task stats","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"boinc_results_all_header":{
        "en":"All BOINC results",
        "ru":"Все результаты BOINC",
        "de":"Alle BOINC Ergebnisse",
        "zh":"所有BOINC结果"
        },
"boinc_results_all_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC",
        "de":"Diese Informationen stammen von verschiedenen BOINC Projekten",
        "zh":"各BOINC项目接收的信息"
        },
"boinc_results_all_table_header_user":{
        "en":{"1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Название","2":"Проект","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"1":"Host Name","2":"Projekt","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"boinc_results_all_table_header_admin":{
        "en":{"0":"Username","1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"0":"Пользователь","1":"Название","2":"Проект","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"},
        "de":{"0":"Nutzername","1":"Host Name","2":"Projekt","3":"7 Tage mag Graph","4":"Mag","5":"&#8776;GRC/Tag"},
        "zh":{"0":"Username","1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"}
        },
"user_control_header":{
        "en":"User control",
        "ru":"Управление пользователями",
        "de":"Benutzerverwaltung",
        "zh":"用户控制"
        },
"user_control_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"user_control_table_header":{
        "en":{"0":"Username","1":"E-mail","2":"Currency","3":"Address","4":"Last sync","5":"Status","6":"Action"},
        "ru":{"0":"Пользователь","1":"Почта","2":"Валюта","3":"Адрес","4":"Посл синхр","5":"Статус","6":"Действие"},
        "de":{"0":"Nutzername","1":"E-Mail","2":"Währung","3":"Adresse","4":"Letzte Synchronisierung","5":"Status","6":"Aktion"},
        "zh":{"0":"Username","1":"E-mail","2":"Currency","3":"Address","4":"Last sync","5":"Status","6":"Action"}
        },
"project_control_header":{
        "en":"Project control",
        "ru":"Управление проектами",
        "de":"Projektverwaltung",
        "zh":"项目控制"
        },
"project_control_desc":{
        "en":"Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).",
        "ru":"Enabled (или auto enabled) означает что данные проекта обновляются и награждение будет начисляться. Stats only - пользователи не могут подключиться к проекту, но награда начисляется. Auto disabled (или disabled) - не обращаться к проекту и никаких вознаграждений.",
        "de":"Aktiviert (oder automatisch aktiviert) bedeutet, dass die Projektdaten aktualisiert werden und Vergütungen vergeben werden. Nur Statistiken - Benutzer können sich nicht mit dem Projekt verbinden, aber die Vergütung wird vergeben. Automatisch deaktiviert (oder deaktiviert) - keinen Zugriff auf das Projekt und keine Vergütung.",
        "zh":"使能（或自动使能）代表项目数据更新和奖金发放。仅统计 - 用户不能连接项目，获取奖金，自动失效 - 仅可以下载统计，没有奖金，失效 - 不能检查项目的任何信息（也没有奖金）"
        },
"project_control_table_header":{
        "en":{"0":"Name","1":"URL","2":"CPID","3":"Weak auth","4":"Team","5":"Last query","6":"Last update","7":"Status","8":"Action"},
        "ru":{"0":"Проект","1":"Ссылка","2":"CPID","3":"Ключ","4":"Команда","5":"Посл опрос","6":"Обновлен","7":"Статус","8":"Действие"},
        "de":{"0":"Name","1":"URL","2":"CPID","3":"Schwacher authentikator","4":"Team","5":"Letzte Anfrage","6":"Letzte Aktualisierung","7":"Status","8":"Aktion"},
        "zh":{"0":"Name","1":"URL","2":"CPID","3":"Weak auth","4":"Team","5":"Last query","6":"Last update","7":"Status","8":"Action"}
        },
"payouts_header":{
        "en":"Payouts",
        "ru":"Выплаты",
        "de":"Auszahlungen",
        "zh":"支付"
        },
"payouts_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"payouts_header":{
        "en":"Payouts",
        "ru":"Выплаты",
        "de":"Zahlungen",
        "zh":"付款方式"
        },
"payout_owes_header":{
        "en":"Pool owes",
        "ru":"Ещё не выплачено",
        "de":"Der Pool schuldet",
        "zh":"矿池拥有者"
        },
"payout_owes_desc":{
        "en":"Pool owes table. These rewards not send yet, because payout limit is not reached.",
        "ru":"Долги пула. Награды ещё не отправлены, потому что не достигнут порог выплаты.",
        "de":"Pool Schuldentabelle. Diese Auszahlungen wurden noch nicht getätigt, da der Minimalbetrag noch nicht erreicht ist.",
        "zh":"矿池拥有列表。奖金没有发送，因为没有达到支付门限"
        },
"project_owes_table_header":{
        "en":{"0":"Address","1":"Currency amount","2":"Interval from","3":"Interval to"},
        "ru":{"0":"Адрес","1":"Сумма","2":"Начало периода","3":"Конец периода"},
        "de":{"0":"Adresse","1":"Betrag","2":"Periodenbeginn","3":"Periodenende"},
        "zh":{"0":"Address","1":"Currency amount","2":"Interval from","3":"Interval to"}
        },
"payout_billings_pre":{
        "en":"Last 10 billings from pool:",
        "ru":"Последние 10 начислений от пула:",
        "de":"Die letzten 10 Auszahlungen des Pools:",
        "zh":"矿池最后10笔账单:"
        },
"payout_billings_header":{
        "en":"For period from %start_date% to %stop_date% pool rewarded with %reward% gridcoins (%comment%)",
        "ru":"За период с %start_date% по %stop_date% пул получил %reward% гридкоинов (%comment%)",
        "de":"Im Zeitraum vom %start_date% bis zum %stop_date% erhielt der Pool %reward% gridcoins (%comment%)",
        "zh":"从 %start_date% 到 %stop_date% 矿池奖励 %reward% gridcoins (%comment%)"
        },
"payout_billings_grc_table_pre":{
        "en":"Gridcoin payouts",
        "ru":"Выплаты в гридкоинах",
        "de":"Gridcoin Auszahlungen",
        "zh":"Gridcoin 支付"
        },
"payout_billings_grc_table_header":{
        "en":{"1":"Address","2":"GRC amount","3":"TX ID","4":"Timestamp"},
        "ru":{"1":"Адрес","2":"Сумма в GRC","3":"Транзакция","4":"Время"},
        "de":{"1":"Adresse","2":"GRC Betrag","3":"TX ID","4":"Zeitstempel"},
        "zh":{"1":"Address","2":"GRC amount","3":"TX ID","4":"Timestamp"}
        },
"payout_billings_alt_table_pre":{
        "en":"Alternative currencies",
        "ru":"Альтернативные валюты",
        "de":"Alternative Währungen",
        "zh":"其他货币"
        },
"payout_billings_alt_table_header":{
        "en":{"1":"Address","2":"Amount","3":"TX ID","4":"Timestamp"},
        "ru":{"1":"Адрес","2":"Сумма","3":"Транзакция","4":"Время"},
        "de":{"1":"Adresse","2":"Betrag","3":"TX ID","4":"Zeitstempel"},
        "zh":{"1":"Address","2":"Amount","3":"TX ID","4":"Timestamp"}
        },
"pool_stats_header":{
        "en":"Pool stats",
        "ru":"Статистика пула",
        "de":"Pool Statistiken",
        "zh":"矿池统计"
        },
"pool_stats_desc":{
        "en":"Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).",
        "ru":"Enabled (или auto enabled) означает что данные проекта обновляются и награждение будет начисляться. Stats only - пользователи не могут подключиться к проекту, но награда начисляется. Auto disabled (или disabled) - не обращаться к проекту и никаких вознаграждений.",
        "de":"Aktiviert (oder automatisch aktiviert) bedeutet, dass die Projektdaten aktualisiert werden und Vergütungen vergeben werden. Nur Statistiken - Benutzer können sich nicht mit dem Projekt verbinden, aber die Vergütung wird vergeben. Automatisch deaktiviert (oder deaktiviert) - keinen Zugriff auf das Projekt und keine Vergütung.",
        "zh":"使能（或自动使能）代表项目数据更新和奖金发放。仅统计 - 用户不能连接项目，获取奖金，自动失效 - 仅可以下载统计，没有奖金，失效 - 不能检查项目的任何信息（也没有奖金）"
        },
"pool_stats_table_header":{
        "en":{"1":"Project","2":"Team RAC","3":"Pool RAC","4":"Pool mag","5":"&#8776;Pool GRC/day","6":"Hosts","7":"Task report","8":"Status","9":"Pool mag 7d graph"},
        "ru":{"1":"Проект","2":"RAC команды","3":"RAC пула","4":"Магнитуда","5":"&#8776;GRC в день","6":"Хосты","7":"Задания","8":"Состояние","9":"Маг за 7 дней"},
        "de":{"1":"Projekt","2":"Team RAC","3":"Pool RAC","4":"Pool mag","5":"&#8776;Pool GRC/Tag","6":"Hosts","7":"Aufgaben","8":"Status","9":"7 Tage Pool mag Graph"},
        "zh":{"1":"Project","2":"Team RAC","3":"Pool RAC","4":"Pool mag","5":"&#8776;Pool GRC/day","6":"Hosts","7":"Task report","8":"Status","9":"Pool mag 7d graph"}
        },
"currencies_header":{
        "en":"Payout currencies",
        "ru":"Валюты для выплаты",
        "de":"Auszahlungswährungen",
        "zh":"支付货币"
        },
"currencies_desc":{
        "en":"Data for payout currencies",
        "ru":"Данные по валютам для выплат",
        "de":"Währungsdaten für Zahlungen",
        "zh":"支付货币数据"
        },
"currencies_table_header":{
        "en":{"1":"Full name","2":"Rate per 1 GRC","3":"Payout limit","4":"TX fee","5":"Project fee"},
        "ru":{"1":"Название","2":"Курс к 1 GRC","3":"Порог выплаты","4":"Комиссия сети","5":"Комиссия проекта"},
        "de":{"1":"Name","2":"Kurs zu 1 GRC","3":"Payout Limit","4":"TX Gebühr","5":"Projekt Gebühr"},
        "zh":{"1":"Full name","2":"Rate per 1 GRC","3":"Payout limit","4":"TX fee","5":"Project fee"}
        },
"feedback_header":{
        "en":"Feedback",
        "ru":"Обратная связь",
        "de":"Feedback",
        "zh":"Feedback"
        },
"feedback_desc":{
        "en":"You can ask questions here or just send a random message to the pool administration. Don't forget to set a reply address if you to get a reply.",
        "ru":"Здесь вы можете задать вопрос или просто отправить сообщение администрации пула. Не забудьте указать адрес для ответа, если расчитываете получить ответ.",
        "de":"Hier kannst du Fragen stellen oder einfach irgendeine Nachricht an den Pooladministrator senden. Vergiss nicht eine Antwortadresse einzutragen, wenn du eine Antwort bekommen möchtest.",
        "zh":"您可以在这里提问或者仅仅给矿池管理者随机信息。如果您想得到回复，请别忘记留下回复邮箱。"
        },
"feedback_email":{
        "en":"Reply to (if you want a reply)",
        "ru":"Почта (если вам нужен ответ)",
        "de":"Antwort an (wenn du eine Antwort möchtest)",
        "zh":"回复邮箱（如果您想得到回复）"
        },
"feedback_submit":{
        "en":"Send",
        "ru":"Отправить",
        "de":"Senden",
        "zh":"发送"
        },
"rating_by_host_mag_header":{
        "en":"Ranking by host magnitude",
        "ru":"Рейтинг по магнитуде хоста",
        "de":"Rangliste nach Host mag",
        "zh":"主机magnitude排名"
        },
"rating_by_host_mag_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"rating_by_host_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Domain name","4":"Summary","5":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Название хоста","4":"Сводка","5":"Магнитуда"},
        "de":{"1":"№","2":"Nutzername","3":"Host Name","4":"Zusammenfassung","5":"Magnitude"},
        "zh":{"1":"№","2":"Username","3":"Domain name","4":"Summary","5":"Magnitude"}
        },
"rating_by_host_project_mag_header":{
        "en":"Ranking by host magnitude",
        "ru":"Рейтинг по магнитуде хоста",
        "de":"Rangliste nach Host mag",
        "zh":"主机magnitude排名"
        },
"rating_by_host_project_mag_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"rating_by_host_project_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Project","4":"Domain name","5":"Summary","6":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Проект","4":"Название хоста","5":"Сводка","6":"Магнитуда"},
        "de":{"1":"№","2":"Nutzername","3":"Projekt","4":"Host name","5":"Zusammenfassung","6":"Magnitude"},
        "zh":{"1":"№","2":"Username","3":"Project","4":"Domain name","5":"Summary","6":"Magnitude"}
        },
"rating_by_user_mag_header":{
        "en":"Ranking by user magnitude",
        "ru":"Рейтинг по магнитуде пользователя",
        "de":"Rangliste nach Nutzer mag",
        "zh":"用户magnitude排名"
        },
"rating_by_user_mag_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"rating_by_user_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Hosts count","4":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Число хостов","4":"Магнитуда"},
        "de":{"1":"№","2":"Nutzername","3":"Host Anzahl","4":"Magnitude"},
        "zh":{"1":"№","2":"Username","3":"Hosts count","4":"Magnitude"}
        },
"rating_by_user_project_mag_header":{
        "en":"Ranking by user magnitude",
        "ru":"Рейтинг по магнитуде пользователя",
        "de":"Rangliste nach Nutzer mag",
        "zh":"用户magnitude排名"
        },
"rating_by_user_project_mag_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"rating_by_user_project_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Project","4":"Hosts count","5":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Проект","4":"Число хостов","5":"Магнитуда"},
        "de":{"1":"№","2":"Nutzername","3":"Projekt","4":"Host Anzahl","5":"Magnitude"},
        "zh":{"1":"№","2":"Username","3":"Project","4":"Hosts count","5":"Magnitude"}
        },
"faucet_header":{
        "en":"Faucet",
        "ru":"Кран",
        "de":"Faucet",
        "zh":"Faucet"
        },
"faucet_desc":{
        "en":"",
        "ru":"",
        "de":"",
        "zh":""
        },
"faucet_only_grc":{
        "en":"You can claim only GRC",
        "ru":"Можно получать только гридкоины",
        "de":"Du kannst nur GRC anfordern",
        "zh":"您仅能获取GRC"
        },
"faucet_already_claimed":{
        "en":"You already received coins today",
        "ru":"Вы уже использовали кран сегодня",
        "de":"Du hast heute schon Coins bekommen",
        "zh":"您今天已获取货币"
        },
"faucet_ready":{
        "en":"You can claim %amount% GRC today (your mag is %magnitude%)",
        "ru":"Вы можете получить %amount% GRC сегодня (ваша магнитуда %magnitude%)",
        "de":"Du kannst heute %amount% GRC anfordern (Deine Mag ist %magnitude%)",
        "zh":"您今天能获取 %amount% GRC (您的magnitude有 %magnitude%)"
        },
"faucet_low_magnitude":{
        "en":"You need a magnitude of at least 1 to use the faucet (your magnitude currently is %magnitude%)",
        "ru":"Необходима магнитуда больше 1 для использования крана (ваша текущая магнитуда %magnitude%)",
        "de":"Du brauchst mindestens eine Magnitude von 1 um das Faucet zu benutzen (aktuell ist deine Magnitude %magnitude%",
        "zh":"您要使用faucet至少需要1个magnitude (您当前magnitude有 %magnitude%)"
        },
"page_footer_text":{
        "en":"Opensource gridcoin pool (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github</a>) by <a href='https://arikado.xyz/'>sau412</a>.",
        "ru":"Пул для майнинга Gridcoin, (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github</a>). Автор - <a href='https://arikado.xyz/'>sau412</a>.",
        "de":"Opensource gridcoin Pool (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github link</a>) von <a href='https://arikado.xyz/'>sau412</a>.",
        "zh":"gridcoin开源矿池 (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github link</a>) by <a href='https://arikado.xyz/'>sau412</a>."
        }
}
_END;

// Load current language
$current_language=lang_load(lang_get_current());
//var_dump($current_language);

// Get current language
function lang_get_current() {
        if(isset($_COOKIE['lang'])) {
                return $_COOKIE['lang'];
        } else {
                return "en";
        }
}

// Returns TRUE if language supported
function lang_if_exists($lang) {
        $possible_lang=array("en","ru","de","zh");
        if(in_array($lang,$possible_lang)) {
                return TRUE;
        } else {
                return FALSE;
        }
}

// Set current language
function lang_set_current($lang) {
        setcookie("lang",$lang,time()+30*24*60*60); // 30 days
}

// Load language into $current_language array
function lang_load($language) {
        global $lang_data_json;
        $current_language=array();
        $lang_data=json_decode($lang_data_json);
        foreach($lang_data as $variable => $data) {
                $current_language[$variable]=$data->$language;
        }
        return $current_language;
}
?>

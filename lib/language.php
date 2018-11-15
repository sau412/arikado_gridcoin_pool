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
        "ru":"pool_info_ru"
        },
"message_logout_success":{
        "en":"Logout successfull",
        "ru":"Вы разлогинились"
        },
"txid_limit_not_reached":{
        "en":"limit not reached",
        "ru":"порог вывода не достигнут"
        },
"menu_pool_info":{
        "en":"Pool info",
        "ru":"Информация"
        },
"menu_login":{
        "en":"Login",
        "ru":"Войти"
        },
"menu_register":{
        "en":"Register",
        "ru":"Зарегистрироваться"
        },
"menu_payouts":{
        "en":"Payouts",
        "ru":"Выплаты"
        },
"menu_rating_by_host_mag":{
        "en":"Rating by host mag",
        "ru":"Рейтинг хостов"
        },
"menu_rating_by_user_mag":{
        "en":"Rating by user mag",
        "ru":"Рейтинг пользователей"
        },
"menu_rating_by_host_project_mag":{
        "en":"Rating by host project mag",
        "ru":"Рейтинг хостов по проектам"
        },
"menu_rating_by_user_project_mag":{
        "en":"Rating by user project mag",
        "ru":"Рейтинг пользователей по проектам"
        },
"menu_pool_stats":{
        "en":"Pool project stats",
        "ru":"Статистика по проектам"
        },
"menu_statistics":{
        "en":"Statistics",
        "ru":"Рейтинги"
        },
"menu_currencies":{
        "en":"Currencies",
        "ru":"Валюты"
        },
"menu_block_explorer":{
        "en":"Block explorer",
        "ru":"Обозреватель блоков"
        },
"menu_info":{
        "en":"Info",
        "ru":"Данные"
        },
"menu_feedback":{
        "en":"Feedback",
        "ru":"Обратная связь"
        },
"menu_settings":{
        "en":"Settings",
        "ru":"Настройки"
        },
"menu_your_hosts":{
        "en":"Your hosts",
        "ru":"Ваши хосты"
        },
"menu_boinc_results_by_host":{
        "en":"Results by host",
        "ru":"Результаты по хостам"
        },
"menu_boinc_results_by_project":{
        "en":"Results by project",
        "ru":"Результаты по проектам"
        },
"menu_boinc_results_by_user":{
        "en":"Results by user",
        "ru":"Результаты по пользователю"
        },
"menu_boinc_results_all_valuable":{
        "en":"All valuable",
        "ru":"Все значимые результаты"
        },
"menu_boinc_results_all":{
        "en":"All results",
        "ru":"Все результаты"
        },
"menu_boinc":{
        "en":"BOINC results",
        "ru":"Результаты BOINC"
        },
"menu_faucet":{
        "en":"Faucet",
        "ru":"Кран"
        },
"menu_user_control":{
        "en":"User control",
        "ru":"Управление пользователями"
        },
"menu_project_control":{
        "en":"Project control",
        "ru":"Управление проектами"
        },
"menu_billing":{
        "en":"Billing",
        "ru":"Биллинг"
        },
"menu_pool_info_editor":{
        "en":"Pool info editor",
        "ru":"Редактор новостей"
        },
"menu_log":{
        "en":"View log",
        "ru":"Просмотр журнала"
        },
"menu_messages_view":{
        "en":"View messages",
        "ru":"Просмотр обратной связи"
        },
"menu_email_view":{
        "en":"View emails",
        "ru":"Просмотр почты"
        },
"menu_control":{
        "en":"Control",
        "ru":"Управление"
        },
"greeting_message":{
        "en":"Welcome,",
        "ru":"Вы зашли как"
        },
"register_header":{
        "en":"Register",
        "ru":"Регистрация"
        },
"register_username":{
        "en":"Username",
        "ru":"Имя пользователя"
        },
"register_password":{
        "en":"Password",
        "ru":"Пароль"
        },
"register_username_after":{
        "en":"required, only letters A-Z, a-z, numbers, dot, dash, underscore",
        "ru":"обязательное поле, допустимы латинские буквы, цифры, точка, подчёркивание, дефис"
        },
"register_password_after":{
        "en":"required at least $pool_min_password_length characters",
        "ru":"необходимо как минимум $pool_min_password_length символов"
        },
"register_retype_password":{
        "en":"Re-type password",
        "ru":"Пароль ещё раз"
        },
"register_email":{
        "en":"E-mail",
        "ru":"Почта"
        },
"register_email_after":{
        "en":"for password recovery (you can write me from that mail, and I send you new password for account)",
        "ru":"для восстановления пароля (вы можете написать мне с этой почты и я отправлю вам новый пароль для аккаунта)"
        },
"register_payout_address":{
        "en":"Payout address",
        "ru":"Адрес для выплат"
        },
"register_payout_currency":{
        "en":"payout currency",
        "ru":"валюта выплат"
        },
"register_payout_currency_after":{
        "en":"both required",
        "ru":"оба обязательны"
        },
"register_submit":{
        "en":"Register",
        "ru":"Зарегистрироваться"
        },
"login_header":{
        "en":"Login",
        "ru":"Вход"
        },
"login_username":{
        "en":"Username",
        "ru":"Имя пользователя"
        },
"login_password":{
        "en":"Password",
        "ru":"Пароль"
        },
"login_submit":{
        "en":"Login",
        "ru":"Войти"
        },
"settings_header":{
        "en":"Settings",
        "ru":"Настройки"
        },
"settings_desc":{
        "en":"GRC payouts are instant, alternative currencies payouts are cumulative and manual. It takes 1-2 days when payout limit reached to send payout (because manual mode now).",
        "ru":"Выплаты в GRC производятся сразу, выплаты в альтернативных валютах производятся вручную (пока) в течение 1-2 дней по достижению порога выплаты."
        },
"settings_note":{
        "en":"Changing alternative (non-GRC) currency or address notice: please note, owed amount linked to address, not to user. If you change address your previous address owed amount will not lost, but won't payed out until payout limit for previous address reached. You can contact admin for manual payout or change address back and receive payout when payout limit reached.",
        "ru":"Предупреждение о смене типа валюты на альтернативный: пожалуйста учтите, что долги пула привязаны к адресу, а не к пользователю. Если вы измените адрес, то сумма не потеряется, но и не будет выплачена пока не достигнут порог выплаты. Но вы всегда можете связаться с администратором ресурса для выплаты, или изменить адрес на старый и достигнуть порога выплаты таким образом."
        },
"settings_email":{
        "en":"E-mail",
        "ru":"Почта"
        },
"settings_email_reports":{
        "en":"send reports to email if task errors found",
        "ru":"отправлять оповещения об ошибках в заданиях"
        },
"settings_payout_address":{
        "en":"Payout address",
        "ru":"Адрес для выплат"
        },
"settings_payout_currency":{
        "en":"currency",
        "ru":"валюта"
        },
"settings_payout_currency_after":{
        "en":"(look notice above)",
        "ru":"(смотри сообщение выше)"
        },
"settings_password":{
        "en":"Password",
        "ru":"Пароль"
        },
"settings_password_after":{
        "en":"the password is required to change settings",
        "ru":"необходим для применения изменений"
        },
"settings_new_password1":{
        "en":"New password",
        "ru":"Новый пароль"
        },
"settings_new_password1_after":{
        "en":"only if you wish to change password",
        "ru":"только если вы хотите сменить пароль"
        },
"settings_new_password2":{
        "en":"Re-type new password",
        "ru":"Новый пароль ещё раз"
        },
"settings_submit":{
        "en":"Update",
        "ru":"Применить"
        },
"user_hosts_header":{
        "en":"Your hosts",
        "ru":"Ваши хосты"
        },
"user_hosts_desc":{
        "en":"That information will be synced to your BOINC client. When attaching new project sync second time after 1-2 minutes to avoid incomplete sync. If you sync correctly, then you see your host in BOINC results after 1-3 hours.",
        "ru":"Эта информация будет синхронизирована с вашим BOINC клиентом. Когда вы подключетеле новый проект синхронизируйтесь дважды, второй раз через 1-2 минуты после первого (во избежание неполной синхронизации). Если синхронизация прошла успешно, вы увидите свои результаты во вкладке статистики BOINC"
        },
"user_hosts_table_header":{
        "en":{"1":"Host info","2":"Projects"},
        "ru":{"1":"Хост","2":"Проекты"}
        },
"boinc_results_by_host_header":{
        "en":"BOINC results by host",
        "ru":"Результаты BOINC по хостам"
        },
"boinc_results_by_host_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC"
        },
"boinc_results_by_host_table_header_user":{
        "en":{"1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Название","2":"CPU","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"boinc_results_by_host_table_header_admin":{
        "en":{"0":"Username","1":"Domain name","2":"CPU","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"0":"Пользователь","1":"Название","2":"CPU","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"boinc_results_by_project_header":{
        "en":"BOINC results by project",
        "ru":"Результаты BOINC по проектам"
        },
"boinc_results_by_project_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC"
        },
"boinc_results_by_project_table_header":{
        "en":{"1":"Project","2":"&Sigma; RAC","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Проект","2":"&Sigma; RAC","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"boinc_results_by_user_header":{
        "en":"BOINC results by pool user",
        "ru":"Результаты BOINC по пользователям пула"
        },
"boinc_results_by_user_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC"
        },
"boinc_results_by_user_table_header":{
        "en":{"1":"Username","2":"Task stats","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Пользователь","2":"Задания","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"boinc_results_all_header":{
        "en":"All BOINC results",
        "ru":"Все результаты BOINC"
        },
"boinc_results_all_desc":{
        "en":"That information we received from various BOINC projects:",
        "ru":"Эта информация получена от проектов BOINC"
        },
"boinc_results_all_table_header_user":{
        "en":{"1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"1":"Название","2":"Проект","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"boinc_results_all_table_header_admin":{
        "en":{"0":"Username","1":"Domain name","2":"Project","3":"Mag 7d graph","4":"Mag","5":"&#8776;GRC/day"},
        "ru":{"0":"Пользователь","1":"Название","2":"Проект","3":"Маг за 7 дней","4":"Магнитуда","5":"&#8776;GRC в день"}
        },
"user_control_header":{
        "en":"User control",
        "ru":"Управление пользователями"
        },
"user_control_desc":{
        "en":"",
        "ru":""
        },
"user_control_table_header":{
        "en":{"0":"Username","1":"E-mail","2":"Currency","3":"Address","4":"Last sync","5":"Status","6":"Action"},
        "ru":{"0":"Пользователь","1":"Почта","2":"Валюта","3":"Адрес","4":"Посл синхр","5":"Статус","6":"Действие"}
        },
"project_control_header":{
        "en":"Project control",
        "ru":"Управление проектами"
        },
"project_control_desc":{
        "en":"Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).",
        "ru":"Enabled (или auto enabled) означает что данные проекта обновляются и награждение будет начисляться. Stats only - пользователи не могут подключиться к проекту, но награда начисляется. Auto disabled (или disabled) - не обращаться к проекту и никаких вознаграждений."
        },
"project_control_table_header":{
        "en":{"0":"Name","1":"URL","2":"CPID","3":"Weak auth","4":"Team","5":"Last query","6":"Last update","7":"Status","8":"Action"},
        "ru":{"0":"Проект","1":"Ссылка","2":"CPID","3":"Ключ","4":"Команда","5":"Посл опрос","6":"Обновлен","7":"Статус","8":"Действие"}
        },
"payouts_header":{
        "en":"Payouts",
        "ru":"Выплаты"
        },
"payouts_desc":{
        "en":"",
        "ru":""
        },
"payout_owes_header":{
        "en":"Pool owes",
        "ru":"Ещё не выплачено"
        },
"payout_owes_desc":{
        "en":"Pool owes table. These rewards not send yet, because payout limit is not reached.",
        "ru":"Долги пула. Награды ещё не отправлены, потому что не достигнут порог выплаты."
        },
"project_owes_table_header":{
        "en":{"0":"Address","1":"Currency amount","2":"Inerval from","3":"Interval to"},
        "ru":{"0":"Адрес","1":"Сумма","2":"Начало периода","3":"Конец периода"}
        },
"payout_billings_pre":{
        "en":"Last 10 billings from pool:",
        "ru":"Последние 10 начислений от пула:"
        },
"payout_billings_header":{
        "en":"For period from %start_date% to %stop_date% pool rewarded with %reward% gridcoins (%comment%)",
        "ru":"За период с %start_date% по %stop_date% пул получил %reward% гридкоинов (%comment%)"
        },
"payout_billings_grc_table_pre":{
        "en":"Gridcoin payouts",
        "ru":"Выплаты в гридкоинах"
        },
"payout_billings_grc_table_header":{
        "en":{"1":"Address","2":"GRC amount","3":"TX ID","4":"Timestamp"},
        "ru":{"1":"Адрес","2":"Сумма в GRC","3":"Транзакция","4":"Время"}
        },
"payout_billings_alt_table_pre":{
        "en":"Alternative currencies",
        "ru":"Альтернативные валюты"
        },
"payout_billings_alt_table_header":{
        "en":{"1":"Address","2":"Amount","3":"TX ID","4":"Timestamp"},
        "ru":{"1":"Адрес","2":"Сумма","3":"Транзакция","4":"Время"}
        },
"pool_stats_header":{
        "en":"Pool stats",
        "ru":"Статистика пула"
        },
"pool_stats_desc":{
        "en":"Enabled (or auto enabled) means project data updated and rewards are on. Stats only - users cannot attach by themselves, rewards on, auto disabled - only downloading stats, no rewards, disabled - do not check anything about this project (no rewards too).",
        "ru":"Enabled (или auto enabled) означает что данные проекта обновляются и награждение будет начисляться. Stats only - пользователи не могут подключиться к проекту, но награда начисляется. Auto disabled (или disabled) - не обращаться к проекту и никаких вознаграждений."
        },
"pool_stats_table_header":{
        "en":{"1":"Project","2":"Team RAC","3":"Pool RAC","4":"Pool mag","5":"&#8776;Pool GRC/day","6":"Hosts","7":"Task report","8":"Status","9":"Pool mag 7d graph"},
        "ru":{"1":"Проект","2":"RAC команды","3":"RAC пула","4":"Магнитуда","5":"&#8776;GRC в день","6":"Хосты","7":"Задания","8":"Состояние","9":"Маг за 7 дней"}
        },
"currencies_header":{
        "en":"Payout currencies",
        "ru":"Валюты для выплаты"
        },
"currencies_desc":{
        "en":"Data for payout currencies",
        "ru":"Данные по валютам для выплат"
        },
"currencies_table_header":{
        "en":{"1":"Full name","2":"Rate per 1 GRC","3":"Payout limit","4":"TX fee","5":"Project fee"},
        "ru":{"1":"Название","2":"Курс к 1 GRC","3":"Порог выплаты","4":"Комиссия сети","5":"Комиссия проекта"}
        },
"block_explorer_header":{
        "en":"Last 500 blocks",
        "ru":"Последние 500 блоков"
        },
"block_explorer_desc":{
        "en":"Data from blockchain (at least 110 confirmations, updated hourly)",
        "ru":"Данные блокчейна (блоки со 110 подтверждениями и более, обновляются ежечасно)"
        },
"block_explorer_table_header":{
        "en":{"1":"Number","2":"Hash","3":"Mint","4":"Ineterst","5":"CPID","6":"Rewards","7":"Timestamp"},
        "ru":{"1":"Номер","2":"Хэш","3":"Всего монет","4":"За блок","5":"CPID","6":"Выплата","7":"Время"}
        },
"feedback_header":{
        "en":"Feedback",
        "ru":"Обратная связь"
        },
"feedback_desc":{
        "en":"You can ask questions here or just send a random message to the pool administration. Don't forget to set a reply address if you to get a reply.",
        "ru":"Здесь вы можете задать вопрос или просто отправить сообщение администрации пула. Не забудьте указать адрес для ответа, если расчитываете получить ответ."
        },
"feedback_email":{
        "en":"Reply to (if you want a reply)",
        "ru":"Почта (если вам нужен ответ)"
        },
"feedback_submit":{
        "en":"Send",
        "ru":"Отправить"
        },
"rating_by_host_mag_header":{
        "en":"Rating by host magnitude",
        "ru":"Рейтинг по магнитуде хоста"
        },
"rating_by_host_mag_desc":{
        "en":"",
        "ru":""
        },
"rating_by_host_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Domain name","4":"Summary","5":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Название хоста","4":"Сводка","5":"Магнитуда"}
        },
"rating_by_host_project_mag_header":{
        "en":"Rating by host magnitude",
        "ru":"Рейтинг по магнитуде хоста"
        },
"rating_by_host_project_mag_desc":{
        "en":"",
        "ru":""
        },
"rating_by_host_project_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Project","4":"Domain name","5":"Summary","6":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Проект","4":"Название хоста","5":"Сводка","6":"Магнитуда"}
        },
"rating_by_user_mag_header":{
        "en":"Rating by user magnitude",
        "ru":"Рейтинг по магнитуде пользователя"
        },
"rating_by_user_mag_desc":{
        "en":"",
        "ru":""
        },
"rating_by_user_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Hosts count","4":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Число хостов","4":"Магнитуда"}
        },
"rating_by_user_project_mag_header":{
        "en":"Rating by user magnitude",
        "ru":"Рейтинг по магнитуде пользователя"
        },
"rating_by_user_project_mag_desc":{
        "en":"",
        "ru":""
        },
"rating_by_user_project_mag_table_header":{
        "en":{"1":"№","2":"Username","3":"Project","4":"Hosts count","5":"Magnitude"},
        "ru":{"1":"№","2":"Пользователь","3":"Проект","4":"Число хостов","5":"Магнитуда"}
        },
"faucet_header":{
        "en":"Faucet",
        "ru":"Кран"
        },
"faucet_desc":{
        "en":"",
        "ru":""
        },
"faucet_only_grc":{
        "en":"You can claim only GRC",
        "ru":"Можно получать только гридкоины"
        },
"faucet_already_claimed":{
        "en":"You already received coins today",
        "ru":"Вы уже использовали кран сегодня"
        },
"faucet_ready":{
        "en":"You can claim %amount% GRC today (your mag is %magnitude%)",
        "ru":"Вы можете получить %amount% GRC сегодня (ваша магнитуда %magnitude%)"
        },
"faucet_low_magnitude":{
        "en":"You need magnutude>=1 for use faucet (your magnitude currently is %magnitude%)",
        "ru":"Необходима магнитуда больше 1 для использования крана (ваша текущая магнитуда %magnitude%)"
        },
"page_footer_text":{
        "en":"Opensource gridcoin pool (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github link</a>) by Vladimir Tsarev, my nickname is sau412 on telegram, twitter, facebook, gmail, github, vk, gridcoin slack and discord.",
        "ru":"Пул для майнинга Gridcoin, (<a href='https://github.com/sau412/arikado_gridcoin_pool'>github</a>). Автор - Царев Владимир, мой ник sau412 в контакте, телеграме, твиттере, фейсбуке, gmail, github, gridcoin slack и discord"
        }
}
_END;

// Load current language
$current_language=lang_load(lang_get_current());
//var_dump($current_language);

// Get current language
function lang_get_current() {
        if(isset($_COOKIE['lang']) && $_COOKIE['lang']=="ru") {
                return "ru";
        } else {
                return "en";
        }
}

// Get next language
function lang_get_next($current) {
        if($current=="en") return "ru";
        return "en";
}

// Returns TRUE if language supported
function lang_if_exists($lang) {
        $possible_lang=array("en","ru");
        if(in_array($lang,$possible_lang)) return TRUE;
        return FALSE;
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

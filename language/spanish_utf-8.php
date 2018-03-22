<?php
/**
*   Language file for the Birthdays plugin for glFusion
*
*   @author     Lee Garner <lee@leegarner.com>
*   @translation spanish    William López Jimenez <william.koalasoft@gmail.com>
*   @author     Mike Lynn <mike@mlynn.com>
*   @copyright  Copyright (c) 2018 Lee Garner <lee@leegarner.com>
*   @copyright  Copyright (c) 2002 Mike Lynn <mike@mlynn.com>
*   @package    birthdays
*   @version    0.1.0
*   @license    http://opensource.org/licenses/gpl-2.0.php
*               GNU Public License v2 or later
*   @filesource
*/
$LANG_BD00 = array (
    'pi_title'      => 'Cumpleaños',
    'my_birthday'   => 'Mi Cumpleaños',
    'birthday'      => 'Cumpleaños',
    'sel_month'     => 'Seleciona el Mes',
    'this_month'    => 'Este Mes',
    'next_month'    => 'Siguiente Mes',
    'none'          => '-- Nada --',
    'all'           => '-- Todos --',
    'uid'           => 'uid',
    'view_all'      => 'Ver Todos',
    'msg_happy_birthday' => 'Happy Birthday, %s!',
    'sub_title'     => 'Notificación de cumpleaños',
    'sub_message'   => 'Hoy es el cumpleaños de %s. Únete a nosotros para decir "¡Feliz cumpleaños!"',
    'sub_reason'   => 'Usted está recibiendo este correo electrónico porque ha elegido recibir una notificación cuando %s tiene un cumpleaños.',
    'sub_unsub'     => 'Para darse de baja de estas notificaciones, haga clic en este enlace',
    'card_title'    => 'Feliz cumpleaños de ' . $_CONF['site_name'] . '!',
    'card_message'  => 'Feliz cumpleaños de %s',
    'unsubscribe'   => 'Darse de baja',
    'email_autogen' => 'Este correo electrónico se generó automáticamente. Por favor no responder a este email.',
);

// Localization of the Admin Configuration UI
$LANG_configsections['birthdays'] = array(
    'label' => 'Cumpleaños',
    'title' => 'Configuración del Plugin Cumpleaños',
);

$LANG_fs['birthdays'] = array(
    'fs_main' => 'Configuración Principal',
);

$LANG_confignames['birthdays'] = array(
    'format'   => 'Mostrar formato de Fecha',
    'login_required' => '¿Autenticación requerida para ver?',
    'login_greeting' => 'Mensaje de bienvenida al iniciar sesión?',
    'enable_subs' => 'Permitir suscripciones a anuncios de cumpleaños?',
    'enable_cards' => 'Habilitar tarjetas de cumpleaños?',
);

$LANG_configselects['birthdays'] = array(
    0 => array('Cierto' => 1, 'Falso' => 0),
);
 
?>

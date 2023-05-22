<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use QU\PowerBiReportingProvider\DataObjects\TrackingOptions;

/**
 * @ilCtrl_IsCalledBy ilPowerBiReportingProviderConfigGUI: ilObjComponentSettingsGUI
 */
class ilPowerBiReportingProviderConfigGUI extends ilPluginConfigGUI
{
    private ilPowerBiReportingProviderPlugin $plugin;
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilSetting $settings;

    private function construct(): void
    {
        global $DIC;

        $this->plugin = ilPowerBiReportingProviderPlugin::getInstance();
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->settings = $DIC->settings();
    }

    public function performCommand(string $cmd): void
    {
        $this->construct();
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            default:
                switch ($cmd) {
                    case 'configure':
                        $this->configure();
                        break;
                    default:
                        $cmd .= 'Cmd';
                        $this->{$cmd}();
                        break;
                }
                break;
        }
    }

    private function configure(): void
    {
        $this->tpl->setContent($this->getConfigurationForm()->getHTML());
    }

    private function getConfigurationForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setTitle($this->plugin->txt('configuration_export'));

        $target_plugin_id = 'lpeventreportqueue';
        $target_plugin_name = 'lpeventreportqueue';
        if ($this->plugin->isPluginInstalled('Cron', 'crnhk', 'LpEventReportQueue')) {
            $link = $this->ctrl->getLinkTargetByClass([
                ilObjComponentSettingsGUI::class
            ], 'showPlugin', null, false, false);

            if (preg_match('/plugin_id=([^&]+)/i', $link) > 0) {
                $link = preg_replace_callback(
                    '/plugin_id=([^&]+)/i',
                    static fn (array $matches): string => 'plugin_id=' . $target_plugin_id,
                    $link
                );

            } else {
                $link .= '&plugin_id=' . $target_plugin_id;
            }

            if (preg_match('/pname=([^&]+)/i', $link) > 0) {
                $link = preg_replace_callback(
                    '/pname=([^&]+)/i',
                    static fn (array $matches): string => 'pname=' . $target_plugin_name,
                    $link
                );
            } else {
                $link .= 'pname=' . $target_plugin_name;
            }

        } else {
            $link="#";
        }

        $form->setDescription(
            sprintf($this->plugin->txt('config_export_desc'), $link)
        );

        $ti = new ilTextInputGUI($this->plugin->txt('export_path'), 'export_path');
        $ti->setInfo($this->plugin->txt('export_path_info'));
        $ti->setRequired(true);
        $ti->setValue($this->settings->get('export_path', '/tmp'));
        $form->addItem($ti);

        $ti = new ilTextInputGUI($this->plugin->txt('export_filename'), 'export_filename');
        $ti->setInfo($this->plugin->txt('export_filename_info'));
        $ti->setRequired(true);
        $ti->setValue($this->settings->get('export_filename', '[Y-m-d]_powbi_export'));
        $form->addItem($ti);

        $ni = new ilNumberInputGUI($this->plugin->txt('export_limit'), 'export_limit');
        $ni->setInfo($this->plugin->txt('export_limit_info'));
        $ni->setValue($this->settings->get('export_limit', '0'));
        $ni->setMinValue(0);
        $ni->setMaxValue(999);
        $form->addItem($ni);

        $trackingOptions = new TrackingOptions();
        $trackingOptions->load();
        foreach ($trackingOptions->getAvailableOptions() as $keyword) {
            $option = $trackingOptions->getOptionByKeyword($keyword);
            if ($option === null) {
                continue;
            }

            $cb = new ilCheckboxInputGUI($this->plugin->txt($keyword), $keyword);
            $cb->setInfo($this->plugin->txt($keyword . '_info'));
            $cb->setValue('1');
            $cb->setChecked($option->isActive());
            if (in_array($keyword, ['id', 'timestamp'])) {
                $cb->setDisabled(true);
            }
            $sub_ti = new ilTextInputGUI($this->plugin->txt($keyword . '_name'), $keyword . '_name');
            $sub_ti->setInfo($this->plugin->txt($keyword . '_name_info'));
            $sub_ti->setValue($option->getFieldName());

            $cb->addSubItem($sub_ti);
            $form->addItem($cb);
        }

        $ignoreNotAttempted = new ilCheckboxInputGUI($this->plugin->txt('ignoreNotAttempted'), 'ignoreNotAttempted');
        $ignoreNotAttempted->setChecked((bool) $this->settings->get('ignoreNotAttempted_' . $this->plugin->getId(), ''));
        $ignoreNotAttempted->setValue('1');
        $form->addItem($ignoreNotAttempted);

        $form->addCommandButton('save', $this->plugin->txt('save'));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    private function saveCmd(): void
    {
        $form = $this->getConfigurationForm();
        $trackingOptions = new TrackingOptions();
        $trackingOptions->load();

        if ($form->checkInput()) {
            foreach ($trackingOptions->getAvailableOptions() as $keyword) {
                $option = $trackingOptions->getOptionByKeyword($keyword);
                if ($option === null) {
                    continue;
                }

                if ($form->getInput($keyword)) {
                    $option->setActive(true);
                } elseif (!in_array($keyword, ['id', 'timestamp'], true)) {
                    $option->setActive(false);
                }

                if ($form->getInput($keyword . '_name')) {
                    $option->setFieldName($form->getInput($keyword . '_name'));
                }
                $option->save();
            }

            if ($form->getInput('export_path')) {
                $this->settings->set('export_path', (string) $form->getInput('export_path'));
            }

            if ($form->getInput('export_filename')) {
                $this->settings->set('export_filename', (string) $form->getInput('export_filename'));
            }

            if ($form->getInput('export_limit') ||
                (is_numeric($form->getInput('export_limit')) && (string) $form->getInput('export_limit') === '0')) {
                $this->settings->set('export_limit', (string) ((int) $form->getInput('export_limit')));
            }

            $this->settings->set(
                'ignoreNotAttempted_' . $this->plugin->getId(),
                (string) ((int) $form->getInput('ignoreNotAttempted'))
            );

            $this->tpl->setOnScreenMessage('success', $this->plugin->txt('saving_invoked'), true);
            $this->ctrl->redirect($this, 'configure');
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }
}

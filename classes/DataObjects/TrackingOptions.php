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

namespace QU\PowerBiReportingProvider\DataObjects;

use Exception;

class TrackingOptions
{
    private ?TrackingOption $track_id = null;
    private ?TrackingOption $track_timestamp = null;
    private ?TrackingOption $track_trigger = null;
    private ?TrackingOption $track_progress = null;
    private ?TrackingOption $track_assignment = null;
    private ?TrackingOption $track_obj_type = null;
    private ?TrackingOption $track_obj_title = null;
    private ?TrackingOption $track_refid = null;
    private ?TrackingOption $track_link = null;
    private ?TrackingOption $track_parent_title = null;
    private ?TrackingOption $track_parent_refid = null;
    private ?TrackingOption $track_user_mail = null;
    private ?TrackingOption $track_user_id = null;
    private ?TrackingOption $track_user_login = null;

    public function getTrackId(): ?TrackingOption
    {
        return $this->track_id;
    }

    public function setTrackId(TrackingOption $track_id): self
    {
        $this->track_id = $track_id;
        return $this;
    }

    public function getTrackTimestamp(): ?TrackingOption
    {
        return $this->track_timestamp;
    }

    public function setTrackTimestamp(TrackingOption $track_timestamp): self
    {
        $this->track_timestamp = $track_timestamp;
        return $this;
    }

    public function getTrackTrigger(): ?TrackingOption
    {
        return $this->track_trigger;
    }

    public function setTrackTrigger(TrackingOption $track_trigger): self
    {
        $this->track_trigger = $track_trigger;
        return $this;
    }

    public function getTrackProgress(): ?TrackingOption
    {
        return $this->track_progress;
    }

    public function setTrackProgress(TrackingOption $track_progress): self
    {
        $this->track_progress = $track_progress;
        return $this;
    }

    public function getTrackAssignment(): ?TrackingOption
    {
        return $this->track_assignment;
    }

    public function setTrackAssignment(TrackingOption $track_assignment): self
    {
        $this->track_assignment = $track_assignment;
        return $this;
    }

    public function getTrackObjType(): ?TrackingOption
    {
        return $this->track_obj_type;
    }

    public function setTrackObjType(TrackingOption $track_obj_type): self
    {
        $this->track_obj_type = $track_obj_type;
        return $this;
    }

    public function getTrackObjTitle(): ?TrackingOption
    {
        return $this->track_obj_title;
    }

    public function setTrackObjTitle(TrackingOption $track_obj_title): self
    {
        $this->track_obj_title = $track_obj_title;
        return $this;
    }

    public function getTrackRefid(): ?TrackingOption
    {
        return $this->track_refid;
    }

    public function setTrackRefid(TrackingOption $track_refid): self
    {
        $this->track_refid = $track_refid;
        return $this;
    }

    public function getTrackLink(): ?TrackingOption
    {
        return $this->track_link;
    }

    public function setTrackLink(TrackingOption $track_link): self
    {
        $this->track_link = $track_link;
        return $this;
    }

    public function getTrackParentTitle(): ?TrackingOption
    {
        return $this->track_parent_title;
    }

    public function setTrackParentTitle(TrackingOption $track_parent_title): self
    {
        $this->track_parent_title = $track_parent_title;
        return $this;
    }

    public function getTrackParentRefid(): ?TrackingOption
    {
        return $this->track_parent_refid;
    }

    public function setTrackParentRefid(TrackingOption $track_parent_refid): self
    {
        $this->track_parent_refid = $track_parent_refid;
        return $this;
    }

    public function getTrackUserMail(): ?TrackingOption
    {
        return $this->track_user_mail;
    }

    public function setTrackUserMail(TrackingOption $track_user_mail): self
    {
        $this->track_user_mail = $track_user_mail;
        return $this;
    }

    public function getTrackUserId(): ?TrackingOption
    {
        return $this->track_user_id;
    }

    public function setTrackUserId(TrackingOption $track_userid): self
    {
        $this->track_user_id = $track_userid;
        return $this;
    }

    public function getTrackUserLogin(): ?TrackingOption
    {
        return $this->track_user_login;
    }

    public function setTrackUserLogin(TrackingOption $track_user_login): self
    {
        $this->track_user_login = $track_user_login;
        return $this;
    }

    public function getOptionByKeyword(string $keyword): ?TrackingOption
    {
        $func_name = $this->getGetterByKeyword($keyword);
        if (method_exists($this, $func_name)) {
            return $this->{$func_name}();
        }

        return null;
    }

    public function load(): bool
    {
        global $DIC;

        /** @var \QU\PowerBiReportingProvider\Logging\Log $logger */
        $logger = $DIC['plugin.powbi.export.cronjob.logger'];

        $load_status = false;

        $available = $this->getAvailableOptions();
        $options = $this->_load();
        if ($options !== []) {
            foreach ($options as $option) {
                if (in_array($option['keyword'], $available, true)) {
                    $tOption = new TrackingOption();

                    $func_name = $this->getSetterByKeyword($option['keyword']);
                    try {
                        if (!$tOption->load($option['id'])) {
                            $tOption->setId((int) $option['id'])
                                ->setKeyword($option['keyword'])
                                ->setActive((int) $option['active'] === 1)
                                ->setFieldName($option['field_name'])
                                ->setUpdatedAt($option['updated_at']);
                        }
                        $logger->debug('loaded option for ' . $option['keyword']);
                        $this->{$func_name}($tOption);

                    } catch (Exception $e) {
                        $load_status = false;
                        $logger->warn(
                            'failure while loading option for ' . $option['keyword'] . ': ' . $e->getMessage()
                        );
                    }
                }
            }
            $load_status = true;
        }

        return $load_status;
    }

    /**
     * @return list<string>
     */
    public function getAvailableOptions(): array
    {
        return [
            'id',
            'timestamp',
            'trigger',
            'progress',
            'assignment',
            'obj_type',
            'obj_title',
            'refid',
            'link',
            'parent_title',
            'parent_refid',
            'user_mail',
            'user_id',
            'user_login',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function _load(): array
    {
        global $DIC;

        $select = 'SELECT * FROM `powbi_prov_options`;';

        $result = $DIC->database()->query($select);

        return $DIC->database()->fetchAll($result);
    }

    private function getSetterByKeyword(string $keyword): string
    {
        $rn = explode('_', $keyword);
        $func_name = 'setTrack';
        if (count($rn) > 1) {
            foreach ($rn as $rn_part) {
                $func_name .= ucfirst($rn_part);
            }
        } else {
            $func_name .= ucfirst($rn[0]);
        }

        return $func_name;
    }

    private function getGetterByKeyword(string $keyword): string
    {
        $rn = explode('_', $keyword);
        $func_name = 'getTrack';
        if (count($rn) > 1) {
            foreach ($rn as $rn_part) {
                $func_name .= ucfirst($rn_part);
            }
        } else {
            $func_name .= ucfirst($rn[0]);
        }

        return $func_name;
    }
}

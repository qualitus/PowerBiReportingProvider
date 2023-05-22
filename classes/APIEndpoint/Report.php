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

namespace QU\PowerBiReportingProvider\APIEndpoint;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use ilPowerBiReportingProviderPlugin;
use QU\LERQ\API\API;
use QU\LERQ\API\Filter\FilterObject;
use QU\LERQ\Model\QueueModel;

class Report
{
    /**
     * @param array<string, mixed> $params
     * @return array{"data": list<\QU\LERQ\Model\QueueModel>}
     */
    public function getFilteredEvents(array $params): array
    {
        global $DIC;

        if (!isset($DIC['autoload.lc.lcautoloader'])) {
            if (ilPowerBiReportingProviderPlugin::getInstance()->isPluginInstalled('Cron', 'crnhk', 'LpEventReportQueue')) {
                ilPowerBiReportingProviderPlugin::getInstance()->getPlugin('Cron', 'crnhk', 'LpEventReportQueue');
            }
        }

        /** @var \QU\LERQ\API\API $API */
        $API = $DIC['qu.lerq.api'];

        $filter = $this->createFilterObject($API, $params);

        /** @var \QU\LERQ\Model\QueueModel $value */
        $list = [];
        foreach ($API->getCollection($filter) as $value) {
            $value->setCourseStart($this->convertToISO8601($value->getCourseStart()));
            $value->setCourseEnd($this->convertToISO8601($value->getCourseEnd()));
            $list[] = $value;
        }

        return [
            'data' => $list,
        ];
    }

    /**
     * @param array<string, mixed> $params
     */
    private function createFilterObject(API $API, array $params): FilterObject
    {
        $filter = $API->createFilterObject();
        if (isset($params['event_before'])) {
            $filter->setEventHappenedStart($this->convertFromISO8601($params['event_before']));
        }
        if (isset($params['event_after'])) {
            $filter->setEventHappenedEnd($this->convertFromISO8601($params['event_after']));
        }
        if (isset($params['course_before'])) {
            $filter->setCourseEnd($this->convertFromISO8601($params['course_before']));
        }
        if (isset($params['course_after'])) {
            $filter->setCourseStart($this->convertFromISO8601($params['course_after']));
        }
        if (isset($params['excluded_progress'])) {
            $filter->setExcludedProgress($params['excluded_progress']);
        }
        if (isset($params['progress'])) {
            $filter->setProgress($params['progress']);
        }
        if (isset($params['trigger'])) {
            $filter->setEvent($params['trigger']);
        }
        if (isset($params['assignment'])) {
            $filter->setAssignment($params['assignment']);
        }
        if (isset($params['start'])) {
            $filter->setPageStart($params['start']);
        }
        if (isset($params['limit'])) {
            $filter->setPageLength($params['limit']);
        }
        if (isset($params['negative_pager'])) {
            $filter->setNegativePager($params['negative_pager']);
        }

        return $filter;
    }

    private function convertFromISO8601(string $iso): int
    {
        return (new DateTimeImmutable($iso))->getTimestamp();
    }

    /**
     * @param mixed $timestamp
     */
    private function convertToISO8601($timestamp): ?string
    {
        if ($timestamp === null) {
            return null;
        }

        $b = new DateTimeImmutable();
        $b->setTimestamp($timestamp);
        $b->setTimezone(new DateTimeZone('UTC'));

        return $b->format('c');
    }
}

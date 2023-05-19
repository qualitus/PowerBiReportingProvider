# PowerBiReportingProvider Plugin

**Table of Contents**

* [Installation](#installation)
  * [Composer](#composer) 
* [Configuration](#configuration)
* [Usage](#usage)
* [Dependencies](#dependencies)
* [TroubleShooting](#troubleshooting)

## Installation

1. Clone from git or download and extract zip file
2. Rename folder to <b>PowerBiReportingProvider</b>
3. Copy folder to <br/>```<ilias root path>/Customizing/global/plugins/Services/Cron/CronHook/```
4. Navigate in your ILIAS installation to <b>Administration -> Plugins</b> and execute
   1. Actions/Update
   2. Actions/Refresh Languages
   3. Actions/Activate

#### Composer

After the plugin files have been installed as described above,
please install the [`composer`](https://getcomposer.org/) dependencies:

```bash
cd Customizing/global/plugins/Services/Cron/CronHook/PowerBiReportingProvider
composer install --no-dev
```

## Configuration

The confiugration for the plugin can be found here: ```Administration -> Plugins -> Actions -> Configure```.<br/>
The configuration for the cron job can be found here: ```Administration -> General -> Cron-Jobs -> PowerBi Export -> Configure```.

## Usage

This plugin works with an cron job. To manually start the export go to: ```Administration -> General -> Cron-Jobs -> PowerBi Export -> Execute```.

## Dependencies

This plugin requires the LpEventReportQueue plugin to be installed and configured.

## Troubleshooting

### 1. The Export does not create a file

Please check if the plugin is configured correct. Also check if the server 
path exists and the server user (mostly "www-data") has write access.<br/>
You may also check if the queue is not empty. The plugin will not create a file, 
if there is no data to export.<br/>
If the export already has created files before, it is possible that there is no 
new data since the last export.

### 2. The Cron-Job failed with "Plugin LpEventReportQueue is not available or not active."

Check if the LpEventReportQueue plugin is installed, configured and activated.

### 3. The Cron-Job stopped with "Task is currently running/locked"

The Cron-Job can't be executed in parallel and this state says thate is 
already running at the moment. If the job should be done already, you 
should check the log file for errors.

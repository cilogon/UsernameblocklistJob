<?php

App::uses("CoJobBackend", "Model");

class UsernameblocklistJob extends CoJobBackend {
  // Required by COmanage Plugins
  public $cmPluginType = "job";

  // Document foreign keys
  public $cmPluginHasMany = array();

  // Validation rules for table elements
  public $validate = array();

  // Current CO Job Object
  private $CoJob;

  // Current CO ID
  private $coId;

  /**
   * Expose menu items.
   * 
   * @return Array with menu location type as key and array of labels, controllers, actions as values.
   */
  public function cmPluginMenus() {
    return array();
  }

  /**
   * Execute the requested Job.
   *
   * @param  int   $coId    CO ID
   * @param  CoJob $CoJob   CO Job Object, id available at $CoJob->id
   * @param  array $params  Array of parameters, as requested via parameterFormat()
   * @throws InvalidArgumentException
   * @throws RuntimeException
   */
  public function execute($coId, $CoJob, $params) {
    $CoJob->update($CoJob->id, null, "full", null);

    $this->CoJob = $CoJob;
    $this->coId = $coId;

    $url = Configure::read('UsernameblocklistJob.blockListUrl');
    $saveFileLoc = Configure::read('UsernameblocklistJob.blockListFile');

    $ch = curl_init($url);

    $fp = fopen($saveFileLoc, 'wb');
    if(!$fp) {
      $jobHistoryRecordKey = substr($saveFileLoc, 0, 64);
      $jobHistoryComment = "Could not open $saveFileLoc for writing";
      $jobHistoryComment = substr($jobHistoryComment, 0, 256);

      $CoJob->CoJobHistoryRecord->record($CoJob->id, $jobHistoryRecordKey, $jobHistoryComment);

      $CoJob->finish($CoJob->id, "", JobStatusEnum::Failed);

      return;
    } 

    $ret = true;

    $ret = $ret && curl_setopt($ch, CURLOPT_FILE, $fp);
    $ret = $ret && curl_setopt($ch, CURLOPT_HEADER, 0);
    if(!$ret) {
      $jobHistoryRecordKey = substr($saveFileLoc, 0, 64);
      $jobHistoryComment = "Error setting cURL options";
      $jobHistoryComment = substr($jobHistoryComment, 0, 256);

      $CoJob->CoJobHistoryRecord->record($CoJob->id, $jobHistoryRecordKey, $jobHistoryComment);

      $CoJob->finish($CoJob->id, "", JobStatusEnum::Failed);

      return;
    } 

    $ret = curl_exec($ch);
    if(!$ret) {
      $jobHistoryRecordKey = substr($saveFileLoc, 0, 64);
      $jobHistoryComment = "Error executing cURL session";
      $jobHistoryComment = substr($jobHistoryComment, 0, 256);

      $CoJob->CoJobHistoryRecord->record($CoJob->id, $jobHistoryRecordKey, $jobHistoryComment);

      $CoJob->finish($CoJob->id, "", JobStatusEnum::Failed);

      return;
    } 

    curl_close($ch);

    fclose($fp);

    $jobHistoryRecordKey = substr($saveFileLoc, 0, 64);
    $jobHistoryComment = "Downloaded URL " . $url;
    $jobHistoryComment = substr($jobHistoryComment, 0, 256);

    $CoJob->CoJobHistoryRecord->record($CoJob->id, $jobHistoryRecordKey, $jobHistoryComment);

    $CoJob->finish($CoJob->id, "", JobStatusEnum::Complete);
  }

  /**
   * Obtain the list of parameters supported by this Job.
   *
   * @since  COmanage Registry v3.3.0
   * @return Array Array of supported parameters.
   */
  public function parameterFormat() {

    $params = array();

    return $params;
  }
}

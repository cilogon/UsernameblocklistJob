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

    $url = 'https://raw.githubusercontent.com/marteinn/The-Big-Username-Blocklist/main/list_raw.txt';

    $ch = curl_init($url);

    $dir = '/srv/comanage-registry/local/';

    $fileName = basename($url);

    $saveFileLoc = $dir . $fileName;

    $fp = fopen($saveFileLoc, 'wb');

    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    curl_exec($ch);

    curl_close($ch);

    fclose($fp);

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

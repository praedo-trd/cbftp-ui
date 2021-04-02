<?php

namespace TRD\Handler;

use TRD\DataProvider\IMDBDataProvider;
use TRD\DataProvider\TVMazeDataProvider;
use TRD\Handler\Handler;
use TRD\Processor\ProcessorResponse;
use TRD\Utility\Locale;

class NewDataHandler extends Handler
{
    public function handle(ProcessorResponse $response)
    {
        $settings = $this->container['models']['settings'];

        $format = new \Chrismou\Irc\TextFormatting\Format;

        if ($response->data['channel'] == $settings->get('data_exchange_channel')) {
            $bits = explode(' ', $response->data['msg']);

            if (in_array($bits[0], array('tvmaze','imdb','ctvmaze','cimdb','tvmazef', 'imdbf'))) {
                $this->container['log']->debug(sprintf('
                    Data-exchange message: <%s> detected in %s
              ', $response->data['msg'], $response->data['channel']));
            } else {
                $this->container['log']->debug(sprintf('
                    Data-exchange message: <*CENSORED*> detected in %s
              ', $response->data['channel']));
            }

            if (sizeof($bits) >= 2) {
                switch ($bits[0]) {

                    case 'tvmaze':

                        $data = array(
                            'namespace' => $bits[0]
                            , 'key' => implode(' ', array_slice($bits, 1, -1))
                            , 'id' => $bits[sizeof($bits)-1],
                        );

                        $tvmaze = new TVMazeDataProvider($this->container);
                        $tvmazeData = $tvmaze->lookupById($data['id']);

                        if (isset($tvmazeData['id']) and $tvmazeData['id'] == $data['id']) {
                            $tvmaze->save($data['key'], $tvmazeData, true);

                            $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                            $command->setData('channel', $response->data['channel']);
                            $command->setData('msg', $format->color('TVMaze', 'yellow') . ' :: ' . $format->color('Updated', 'green') . ' :: ' . $tvmazeData['title'] . ' ' . sprintf('(%d/%s/%s/%s)', $tvmazeData['id'], $tvmazeData['classification'], $tvmazeData['country'], $tvmazeData['language']));
                            $response->setCommand($command);
                            $response->terminate = true;
                        }

                    break;

                    case 'tvmazef':

                      $value = implode(' ', array_slice($bits, 3));

                      $castedValue = $value;
                      if ($bits[2] === 'genres') {
                          $castedValue = explode(',', $value);
                      } elseif (in_array($bits[2], array('daily','web'))) {
                          $castedValue = $value === "true" ? true : false;
                      }

                      $data = array(
                        'namespace' => 'tvmaze',
                        'id' => $bits[1]
                        ,'field' => $bits[2]
                        ,'value' => $castedValue
                      );

                      $existing = $this->container['db']->fetchAssoc("SELECT data,data_immutable FROM data_cache WHERE namespace = ? AND id = ?", array($data['namespace'], $data['id']));

                      if (empty($existing['data'])) {
                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('TVMaze', 'yellow') . ' :: ' . $format->color('Failed', 'red') . ' :: No data found for this show');
                          $response->setCommand($command);
                          $response->terminate = true;
                          return $response;
                      }

                      $immutableData = array();

                      if (!empty($existing['data_immutable'])) {
                          $immutableData = unserialize($existing['data_immutable']);
                      }

                      // check field is valid
                      $validField = false;
                      $existingData = unserialize($existing['data']);
                      foreach ($existingData as $k => $v) {
                          if ($k == $data['field']) {
                              $validField = true;
                          }
                      }

                      if ($validField) {
                          $immutableData[$data['field']] = $data['value'];

                          if ($data['field'] === 'country') {
                              $key = array_search($data['value'], Locale::COUNTRIES);
                              if ($key !== false) {
                                  $immutableData['country_code'] = $key;
                              }
                          } elseif ($data['field'] === 'country_code') {
                              if (isset(Locale::COUNTRIES[$data['value']])) {
                                  $immutableData['country'] = Locale::COUNTRIES[$data['value']];
                              }
                          }

                          $this->container['db']->update('data_cache', array(
                          'data_immutable' => serialize($immutableData)
                        ), array(
                          'namespace' => $data['namespace'], 'id' => $data['id']
                        ));

                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('TVMaze', 'yellow') . ' :: ' . $format->color('Updated', 'green') . ' :: ' . $existingData['title'] . ' ' . sprintf("%s: %s", $data['field'], $value));
                          $response->setCommand($command);
                          $response->terminate = true;
                      } else {
                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('TVMaze', 'yellow') . ' :: ' . $format->color('Failed', 'red') . ' :: invalid field');
                          $response->setCommand($command);
                          $response->terminate = true;
                      }

                    break;

                    case 'imdb':

                        $data = array(
                            'namespace' => $bits[0]
                            , 'key' => implode(' ', array_slice($bits, 1, -1))
                            , 'id' => $bits[sizeof($bits)-1],
                        );

                        $imdb = new IMDBDataProvider($this->container);
                        $imdbData = $imdb->extractDataFromIMDBId($data['id']);
                        if (isset($imdbData['id']) and $imdbData['id'] == $data['id']) {
                            $imdb->save($data['key'], $imdbData, true);

                            $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                            $command->setData('channel', $response->data['channel']);
                            $command->setData('msg', $format->color('IMDB', 'yellow') . ' :: ' . $format->color('Updated', 'green') . ' :: ' . $imdbData['title'] . ' ' . sprintf('(%s/%s/%s/%s/%s)', $imdbData['id'], $imdbData['year'], implode(',', $imdbData['genres']), $imdbData['country'], $imdbData['language_primary']));
                            $response->setCommand($command);
                            $response->terminate = true;
                        }
                    break;

                    case 'imdbf':

                      $value = implode(' ', array_slice($bits, 3));

                      $data = array(
                        'namespace' => 'imdb',
                        'id' => $bits[1]
                        ,'field' => $bits[2]
                        ,'value' => (in_array($bits[2], array('genres', 'countries', 'languages')) ? explode(',', $value) : $value)
                      );

                      $existing = $this->container['db']->fetchAssoc("SELECT data,data_immutable FROM data_cache WHERE namespace = ? AND id = ?", array($data['namespace'], $data['id']));

                      if (empty($existing['data'])) {
                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('IMDB', 'yellow') . ' :: ' . $format->color('Failed', 'red') . ' :: No data found for this movie');
                          $response->setCommand($command);
                          $response->terminate = true;
                          return $response;
                      }

                      $immutableData = array();

                      if (!empty($existing['data_immutable'])) {
                          $immutableData = unserialize($existing['data_immutable']);
                      }

                      // check field is valid
                      $validField = false;
                      $existingData = unserialize($existing['data']);
                      foreach ($existingData as $k => $v) {
                          if ($k == $data['field']) {
                              $validField = true;
                          }
                      }

                      if ($validField) {
                          $immutableData[$data['field']] = $data['value'];

                          $this->container['db']->update('data_cache', array(
                          'data_immutable' => serialize($immutableData)
                        ), array(
                          'namespace' => $data['namespace'], 'id' => $data['id']
                        ));

                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('IMDB', 'yellow') . ' :: ' . $format->color('Updated', 'green') . ' :: ' . $existingData['title'] . ' ' . sprintf("%s: %s", $data['field'], $value));
                          $response->setCommand($command);
                          $response->terminate = true;
                      } else {
                          $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                          $command->setData('channel', $response->data['channel']);
                          $command->setData('msg', $format->color('IMDB', 'yellow') . ' :: ' . $format->color('Failed', 'red') . ' :: invalid field');
                          $response->setCommand($command);
                          $response->terminate = true;
                      }

                    break;

                    case 'ctvmaze':

                        $tvmaze = new TVMazeDataProvider($this->container);
                        $title = implode(' ', array_slice($bits, 1));
                        $tvmazeDataResponse = $tvmaze->lookup($title);

                        $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                        $command->setData('channel', $response->data['channel']);
                        $tvmazeData = $tvmazeDataResponse->getData();
                        if ($tvmazeDataResponse->result) {
                            $command->setData('msg', $format->color('TVMaze', 'yellow') . ' :: ' . $tvmazeData['title'] . ' ' . sprintf('(%d/%s/%s/%s)', $tvmazeData['id'], $tvmazeData['classification'], $tvmazeData['country'], $tvmazeData['language']));
                        } else {
                            $command->setData('msg', 'TVMaze :: Nothing found for: ' . $title);
                        }
                        $response->setCommand($command);
                        $response->terminate = true;

                    break;

                    case 'cimdb':

                        $imdb = new IMDBDataProvider($this->container);
                        $title = implode(' ', array_slice($bits, 1));
                        $imdbDataResponse = $imdb->lookup($title);
                        $imdbData = $imdbDataResponse->getData();

                        $command = new \TRD\Processor\ProcessorResponseCommand('IRCREPLY');
                        $command->setData('channel', $response->data['channel']);
                        if ($imdbDataResponse->result) {
                            $command->setData('msg', $format->color('IMDB', 'yellow') . ' :: ' . $imdbData['title'] . ' ' . sprintf('(%s/%s/%s/%s/%s)', $imdbData['id'], $imdbData['year'], implode(',', $imdbData['genres']), $imdbData['country'], $imdbData['language_primary']));
                        } else {
                            $command->setData('msg', 'IMDB :: Nothing found for: ' . $title);
                        }
                        $response->setCommand($command);
                        $response->terminate = true;

                    break;


                }
            }
        }

        return $response;
    }
}

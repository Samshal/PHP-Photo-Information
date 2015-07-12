<?php
	class PhotoInfo
	{
		protected $requestObject, $apiKey, $responseType, $returnType, $apiLocation, $responseReturned;
		private $features = array(), $imageLocation;
		public function __construct(Unirest\Request $urObj)
		{
			try
			{
				if (parse_ini_file("config.ini"))
				{
					$config_data = parse_ini_file("config.ini");
				}
				else
				{
					throw new \Exception("Invalid Configuration File Supplied or No Configuration File Was Provided");
				}
			}
			catch(Exception $e)
			{
				print_r($e);
				return false;
			}

			$this->apiKey = $config_data["ApiKey"];
			$this->responseType = $config_data["ResponseType"];
			$this->returnType = $config_data["ReturnType"];
			$this->apiLocation = $config_data["ApiLocation"];
			$this->requestObject = $urObj;
		}

		public function execute($query)
		{
			$forUrl = explode(" ", $query);
			$query = explode(" ", strtolower($query));
			try
			{
				if ($query[0] != "select")
				{
					throw new \Exception("The execute method expected a string parameter that begins with the 'select' keyword");
				}
				foreach ($query as $key => $value)
				{
					$query[$key] = str_replace(",", "", $value);
				}
				if (in_array("*", $query))
				{
					$this->features[] = "*";
				}
				else
				{
					function inArray($k, $array)
					{
						foreach($k as $key)
						{
							if (in_array($key, $array))
							{
								$params[] = $key;
							}
						}
						return (isset($params)) ? $params : false;
					}
					$this->features = inArray(array("age", "gender", "glass", "race", "smiling", "pose"), $query);
				}
				if (empty($this->features))
				{
					throw new \Exception("Please supply a query that contains at least one supported feature to the execute method");
				}

				if (!in_array("from", $query))
				{
					throw new \Exception("The execute method expected a string parameter that contains the 'from' keyword, none was found");
				}

				$this->imageLocation = $forUrl[array_search("from", $query) + 1];

				return $this->getPhotoInfo();
			}
			catch(Exception $e)
			{
				return $e;
			}
		}

		private function setParameters()
		{
			$urlParams = array("X-Mashape-Key"=>$this->apiKey, "Accept"=>$this->responseType);
			return array($this->apiLocation, $urlParams);
		}

		protected function getPhotoInfo()
		{
			$attributeString = "";
			if (!in_array("*", $this->features))
			{
				foreach ($this->features as $key=>$value)
				{
					if ($key == 0)
					{
						$attributeString .= $value;
					}
					else
					{
						$attributeString .= ",".$value;
					}
				}
			}
			else
			{
				$attributeString .= "age,gender,race,pose,smiling,glass";
			}
			$imageLocation = str_replace("/", "%2F", str_replace(":", "%3A", $this->imageLocation));
			$urlString = self::setParameters()[0] . "?attribute=".urlencode("$attributeString")."&url=".$imageLocation;
			$urlParams = self::setParameters()[1];
			$response= $this->requestObject->get($urlString, $urlParams);
			$this->responseReturned = self::parseResponse($response);
			return $this->responseReturned;
		}

		protected function parseResponse($jsonObject)
		{
			try
			{
				$error = json_decode($jsonObject->raw_body);
				if (isset($error->error))
				{
					$msg = $error->error;
					throw new Exception("An Error Occurred: $msg");
					return;
				}
			}
			catch(Exception $e)
			{
				echo $e."<br/>";
				return;
			}
			switch(strtolower($this->returnType))
			{
				case "array":
				{
					$respObject_code = json_decode($jsonObject->code);
					$respObject_rawBody = json_decode($jsonObject->raw_body);
					$respObject = array("code"=>$respObject_code, "raw_body"=>$respObject_rawBody);
					break;
				}
				case "json":
				{
					$respObject = $jsonObject;
					break;
				}
				default:
				{
					throw new \Exception("An invalid return type was specified in the configuration file");
					return;
				}
			}
			if (isset($respObject))
			{
				return $respObject;
			}
			throw new \Exception("Please specify a valid response type in the configuration file");
		}


		private function getResponseBody()
		{
			return (isset($this->responseReturned["raw_body"]->face[0])) ? $this->responseReturned["raw_body"]->face[0] : null;
		}

		private function stopError($param)
		{
			try
			{
				if (isset($param))
				{
					return $param;
				}
				else
				{
					throw new \Exception("Please Specify This Feature In The Query To View It");
				}
			}
			catch (Exception $e)
			{
				return $e;
			}
		}

		public function getAge()
		{
			$value = $this->stopError(self::getResponseBody()->attribute->age->value);
			$range = $this->stopError(self::getResponseBody()->attribute->age->range);
			return array("value"=>$value, "range"=>$range);
		}

		public function getGender()
		{
			$value = $this->stopError(self::getResponseBody()->attribute->gender->value);
			$confidence = $this->stopError(self::getResponseBody()->attribute->gender->confidence);
			return array("value"=>$value, "confidence"=>$confidence);
		}

		public function getPose()
		{
			$pitch_angle = $this->stopError(self::getResponseBody()->attribute->pose->pitch_angle->value);
			$roll_angle = $this->stopError(self::getResponseBody()->attribute->pose->roll_angle->value);
			$yaw_angle = $this->stopError(self::getResponseBody()->attribute->pose->yaw_angle->value);

			return array("pitch_angle"=>$pitch_angle, "roll_angle"=>$roll_angle, "yaw_angle"=>$yaw_angle);
		}

		public function getRace()
		{
			$value = $this->stopError(self::getResponseBody()->attribute->race->value);
			$confidence = $this->stopError(self::getResponseBody()->attribute->race->confidence);
			return array("value"=>$value, "confidence"=>$confidence);
		}

		public function getGlass()
		{
			$value = $this->stopError(self::getResponseBody()->attribute->glass->value);
			$confidence = $this->stopError(self::getResponseBody()->attribute->glass->confidence);
			return array("value"=>$value, "confidence"=>$confidence);
		}

		public function getSmile()
		{
			$value = $this->stopError(self::getResponseBody()->attribute->smiling->value);
			return $value;
		}

		public function getImageHeight()
		{
			$value = $this->stopError($this->responseReturned["raw_body"]->img_height);
			return $value;
		}

		public function getImageWidth()
		{
			$value = $this->stopError($this->responseReturned["raw_body"]->img_width);
			return $value;
		}

		public function getImageSrc()
		{
			$value = $this->stopError($this->responseReturned["raw_body"]->url);
			return $value;
		}

		public function getImageID()
		{
			$value = $this->stopError($this->responseReturned["raw_body"]->img_id);
			return $value;
		}
	}
<?php

class AtomReader
{
	public function __construct(String $feedURL = null)
	{
		$this->feedURL = $feedURL;
		$this->data = new SimpleXMLElement(str_replace("media:thumbnail", "thumbnail", file_get_contents($feedURL)));
	}

	public function title()
	{
		return $this->data->title ?? "Title N/A";
	}

	public function articles()
	{
		$data = [];
		foreach ($this->data->entry as $key => $value) {
			array_push($data, $value);
		}
		return $data;
	}
}
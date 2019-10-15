<?php
// XML parser for BOINC
function xml_parse_user_request($xml) {
	$result=array();

	$xml=str_replace(array("\n","\r"),"",$xml);
	$xml=str_replace("<","\n<",$xml);
	$xml=str_replace(">",">\n",$xml);

	$strings_array=explode("\n",$xml);
	$global_tags_array=array("name","password_hash","host_cpid");
	$project_tags_array=array("project_name","account_key","hostid");
	$host_tags_array=array("domain_name","host_cpid","p_model","p_ncpus","n_usable_coprocs","os_name","os_version","virtualbox_version","product_name");
	$gpu_tags_array=array("count","name");
	$project_index=0;
	$gpu_index=0;
	$tag_flag=FALSE;
	$section="global";
	foreach($strings_array as $str) {
		$str=trim($str);
		// Projects section
		if(preg_match("/^<project>$/",$str)) {
			$section="projects";
			$project_index++;
		}
		else if(preg_match("/^<working_global_preferences>$/",$str)) {
			$section="working_global_preferences";
		}
		// Host info section
		else if(preg_match("/^<host_info>$/",$str)) {
			$section="host_info";
		}
		// GPU section
		else if(preg_match("/^<coproc_intel_gpu>$/",$str)) {
			$section="gpu_info";
			$gpu_index++;
		}
		else if(preg_match("/^<coproc_cuda>$/",$str)) {
			$section="gpu_info";
			$gpu_index++;
		}
		else if(preg_match("/^<coproc_ati>$/",$str)) {
			$section="gpu_info";
			$gpu_index++;
		}
		// End of GPU section
		else if(preg_match("/^<\\/coproc_intel_gpu>$/",$str)) {
			$section="global";
			$gpu_index++;
		}
		else if(preg_match("/^<\\/coproc_cuda>$/",$str)) {
			$section="global";
			$gpu_index++;
		}
		else if(preg_match("/^<\\/coproc_ati>$/",$str)) {
			$section="global";
			$gpu_index++;
		}
		if($section=="global") {
			if($tag_flag==FALSE) {
				foreach($global_tags_array as $tag) {
					if(preg_match("/^<($tag)>\$/",$str,$matches)) {
						$tag_flag=$matches[1];
					}
				}
			} else {
				if(!isset($result[$tag_flag])) $result[$tag_flag]=$str;
				$tag_flag=FALSE;
			}
		}
		else if($section=="projects") {
			if($tag_flag==FALSE) {
				foreach($project_tags_array as $tag) {
					if(preg_match("/^<($tag)>\$/",$str,$matches)) {
						$tag_flag=$matches[1];
					}
				}
			} else {
				$result["projects"][$project_index][$tag_flag]=$str;
				$tag_flag=FALSE;
			}
		}
		else if($section=="host_info") {
			if($tag_flag==FALSE) {
				foreach($host_tags_array as $tag) {
					if(preg_match("/^<($tag)>\$/",$str,$matches)) {
						$tag_flag=$matches[1];
					}
				}
			} else {
				$result[$tag_flag]=$str;
				$tag_flag=FALSE;
			}
		}
		else if($section=="gpu_info") {
			if($tag_flag==FALSE) {
				foreach($gpu_tags_array as $tag) {
					if(preg_match("/^<($tag)>\$/",$str,$matches)) {
						$tag_flag=$matches[1];
					}
				}
			} else {
				$result["gpus"][$gpu_index][$tag_flag]=$str;
				$tag_flag=FALSE;
			}
		}
	}
	return $result;
}

?>

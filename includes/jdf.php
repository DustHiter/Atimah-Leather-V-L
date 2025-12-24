<?php

/*
	Farsi Jalali (Shamsi) Date and Time Functions
	Copyright (C) 2000-2019 Sallar Kaboli (http://sallar.kaboli.org)
	Latest version available at: http://jdf.scr.ir

	LICENSE: FREE FOR NON-COMMERCIAL USE
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

*/

function jdate($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
{

$T_sec=0;/* <= آرشیو شود */

if($time_zone != 'local') date_default_timezone_set(($time_zone === '') ? 'Asia/Tehran' : $time_zone);
$ts=$T_sec+(($timestamp==='') ? time() : tr_num($timestamp));
$date=explode(' _ ',date('H_i_j_n_O_P_s_w_Y',$ts));
list($j_y,$j_m,$j_d)=gregorian_to_jalali($date[8],$date[3],$date[2]);
$doy=($j_m<7)?(($j_m-1)*31)+$j_d-1:(($j_m-7)*30)+$j_d+185;
$kab=(((($j_y%33)%4)-1)%4==0);
$sl=strlen($format);
$out='';
for($i=0; $i<$sl; $i++)
{
	$sub=substr($format,$i,1);
	if($sub=='\\')
	{
		$out.=substr($format,++$i,1);
		continue;
	}
	switch($sub)
	{

		case 'a':$out.=($date[0]<12)?'ق.ظ':'ب.ظ';break;
		case 'A':$out.=($date[0]<12)?'قبل از ظهر':'بعد از ظهر';break;
		case 'B':$out.=floor(1+($ts/86400));break;
		case 'c':$out.=date('Y-m-d\TH:i:sP',$ts);break;
		case 'd':$out.=($j_d<10)?'0'.$j_d:$j_d;break;
		case 'D':$out.=jdate_words(array('rh'=>$date[7]),' ');break;
		case 'F':$out.=jdate_words(array('mm'=>$j_m),' ');break;
		case 'g':$out.=($date[0]>12)?$date[0]-12:$date[0];break;
		case 'G':$out.=$date[0];break;
		case 'h':$out.=((($date[0]>12)?$date[0]-12:$date[0])<10)?'0'.(($date[0]>12)?$date[0]-12:$date[0]):(($date[0]>12)?$date[0]-12:$date[0]);break;
		case 'H':$out.=($date[0]<10)?'0'.$date[0]:$date[0];break;
		case 'i':$out.=($date[1]<10)?'0'.$date[1]:$date[1];break;
		case 'j':$out.=$j_d;break;
		case 'l':$out.=jdate_words(array('rh'=>$date[7]),' ');break;
		case 'L':$out.=$kab;break;
		case 'm':$out.=($j_m<10)?'0'.$j_m:$j_m;break;
		case 'M':$out.=jdate_words(array('mn'=>$j_m),' ');break;
		case 'n':$out.=$j_m;break;
		case 'N':$out.=$date[7]+1;break;
		case 'o':
		$j_y_plus= $j_y+1;
		$j_y_minus= $j_y-1;
		$fall_start=gregorian_to_jalali(date('Y', $ts),3,21);
		$fall_end=gregorian_to_jalali(date('Y', $ts),12,21);
		if($j_d > $fall_start[2] && $j_m <4)
		{
			$out.=$j_y_minus;
		}
		elseif($j_d < $fall_end[2] && $j_m > 10)
		{
			$out.=$j_y_plus;
		}
		else
		{
			$out.=$j_y;
		}
		break;
		case 'O':$out.=$date[4];break;
		case 'p':$out.=jdate_words(array('mb'=>$j_m),' ');break;
		case 'P':$out.=$date[5];break;
		case 'q':$out.=jdate_words(array('sh'=>$j_y),' ');break;
		case 'r':$out.=jdate_words(array('rh'=>$date[7]),' ') .'، ' . $j_d.' ' . jdate_words(array('mm'=>$j_m),' ') .' ' . $j_y .' ' . $date[0].':' . $date[1].':' . $date[6] .' ' . $date[4];break;
		case 's':$out.=($date[6]<10)?'0'.$date[6]:$date[6];break;
		case 'S':$out.='ام';break;
		case 't':$out.=($j_m!=12)?(31-(int)($j_m/6.5)):($kab+29);break;
		case 'U':$out.=$ts;break;
		case 'w':$out.=$date[7];break;
		case 'W':$out.=(int)($doy/7);break;
		case 'y':$out.=substr($j_y,2,2);break;
		case 'Y':$out.=$j_y;break;
		case 'z':$out.=$doy;break;
		default:$out.=$sub;
	}
}
return ($tr_num!='en')?tr_num($out):$out;
}

function jstrftime($format, $timestamp = '', $none = '', $time_zone = 'Asia/Tehran', $tr_num = 'fa')
{

$T_sec=0;/* <= آرشیو شود */

if($time_zone != 'local') date_default_timezone_set(($time_zone === '') ? 'Asia/Tehran' : $time_zone);
$ts=$T_sec+(($timestamp==='')?time():tr_num($timestamp));
$date=explode(' _ ',date('h_H_i_j_n_s_w_Y',$ts));
list($j_y,$j_m,$j_d)=gregorian_to_jalali($date[7],$date[4],$date[3]);
$doy=($j_m<7)?(($j_m-1)*31)+$j_d-1:(($j_m-7)*30)+$j_d+185;
$kab=(((($j_y%33)%4)-1)%4==0);
$sl=strlen($format);
$out='';
for($i=0; $i<$sl; $i++)
{
	$sub=substr($format,$i,1);
	if($sub=='%')
	{
		$sub=substr($format,++$i,1);
	}
	else
	{
		$out.=$sub;
		continue;
	}
	switch($sub)
	{

		case 'a':$out.=jdate_words(array('rh'=>$date[6]),' ');break;
		case 'A':$out.=jdate_words(array('RL'=>$date[6]),' ');break;
		case 'b':$out.=jdate_words(array('mm'=>$j_m),' ');break;
		case 'B':$out.=jdate_words(array('MM'=>$j_m),' ');break;
		case 'c':$out.=jdate('D M j H:i:s Y');break;
		case 'C':$out.=(int)($j_y/100);break;
		case 'd':$out.=($j_d<10)?'0'.$j_d:$j_d;break;
		case 'D':$out.=substr($j_y,2,2).'/' .( ($j_m<10)?'0'.$j_m:$j_m ).'/' .( ($j_d<10)?'0'.$j_d:$j_d );break;
		case 'e':$out.=($j_d<10)?' '.$j_d:$j_d;break;
		case 'H':$out.=($date[1]<10)?'0'.$date[1]:$date[1];break;
		case 'I':$out.=($date[0]<10)?'0'.$date[0]:$date[0];break;
		case 'j':$out.=($doy<100)?(($doy<10)?'00'.$doy:'0'.$doy):$doy;break;
		case 'm':$out.=($j_m<10)?'0'.$j_m:$j_m;break;
		case 'M':$out.=($date[2]<10)?'0'.$date[2]:$date[2];break;
		case 'p':$out.=($date[1]<12)?'قبل از ظهر':'بعد از ظهر';break;
		case 'P':$out.=($date[1]<12)?'ق.ظ':'ب.ظ';break;
		case 's':$out.=floor($ts);break;
		case 'S':$out.=($date[5]<10)?'0'.$date[5]:$date[5];break;
		case 'u':$out.=$date[6]+1;break;
		case 'U':$out.=(int)($doy/7);break;
		case 'V':$out.=(int)($doy/7);break;
		case 'w':$out.=$date[6];break;
		case 'W':$out.=(int)($doy/7);break;
		case 'x':$out.=substr($j_y,2,2).'/' .( ($j_m<10)?'0'.$j_m:$j_m ).'/' .( ($j_d<10)?'0'.$j_d:$j_d );break;
		case 'X':$out.=($date[0]<10)?'0'.$date[0]:$date[0].':' .( ($date[1]<10)?'0'.$date[1]:$date[1] ).':' .( ($date[6]<10)?'0'.$date[6]:$date[6] );break;
		case 'y':$out.=substr($j_y,2,2);break;
		case 'Y':$out.=$j_y;break;
		case 'Z':$out.=date('T',$ts);break;
		case '%':$out.='%';break;

		default:$out.=$sub;
	}
}
return ($tr_num!='en')?tr_num($out):$out;
}

function gregorian_to_jalali($gy,$gm,$gd,$mod='')
{
	$g_d_m=array(0,31,59,90,120,151,181,212,243,273,304,334);
	$gy2=($gm>2)?($gy+1):$gy;
	$days=355666+(365*$gy)+((int)(($gy2+3)/4))-((int)(($gy2+99)/100))+((int)(($gy2+399)/400))+$gd+$g_d_m[$gm-1];
	$jy=-1595+(33*((int)($days/12053)));
	$days%=12053;
	$jy+=4*((int)($days/1461));
	$days%=1461;
	if($days > 365)
	{
		$jy+=(int)(($days-1)/365);
		$days=($days-1)%365;
	}
	$jm=($days<186)?1+(int)($days/31):7+(int)(($days-186)/30);
	$jd=1+(($days<186)?($days%31):(($days-186)%30));
	return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
}

function jalali_to_gregorian($jy,$jm,$jd,$mod='')
{
	$jy+=1595;
	$days=-355668+(365*$jy)+(((int)($jy/33))*8)+((int)((($jy%33)+3)/4));
	if((($jy%33)%4)==0 and $gy%100!=0 and $gy%400!=0){$days++;}
	$jd+=($jm<7)?($jm-1)*31:(($jm-7)*30)+186;
	$days+=$jd;
	$gy=400*((int)($days/146097));
	$days%=146097;
	if($days > 36524)
	{
		$gy+=100*((int)(--$days/36524));
		$days%=36524;
		if($days >= 365){$days++;}
	}
	$gy+=4*((int)($days/1461));
	$days%=1461;
	$gy+=(int)(($days-1)/365);
	$days=($days-1)%365;
	$gd=$days+1;
	foreach(array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy%400==0))?29:28,31,30,31,30,31,31,30,31,30,31) as $gm=>$v)
	{
		if($gd<=$v)break;
		$gd-=$v;
	}
	return($mod=='')?array($gy,$gm,$gd):$gy.$mod.$gm.$mod.$gd;
}

function jdate_words($array,$mod='')
{
foreach($array as $type=>$num)
{
	$num=(int)tr_num($num);
	switch($type)
	{
		case 'ss':
		$sl=strlen($num);
		$xy3=substr($num,2-$sl,1);
		$h3=jdate_words(array('h'=>$xy3));
		$h34=jdate_words(array('h'=>$xy3+1));
		$xy4=substr($num,3-$sl,1);
		$h4=jdate_words(array('h'=>$xy4));
		$h44=jdate_words(array('h'=>$xy4+1));
		if($sl==4)
		{
			$f=($num<2000)?(($num<1400 and $num>1299)?jdate_words(array('h'=>13)).'صد و '.jdate_words(array('ss'=>substr($num,2,2))):''):jdate_words(array('h'=>substr($num,0,1))).' هزار و '.jdate_words(array('ss'=>substr($num,1,3)));
		}
		elseif($sl==3)
		{
			$f=($num<200)?jdate_words(array('h'=>1)).'صد و '.jdate_words(array('ss'=>substr($num,1,2))):jdate_words(array('h'=>substr($num,0,1))).'صد و '.jdate_words(array('ss'=>substr($num,1,2)));
		}
		elseif($sl==2)
		{
			$f=($num>9 and $num<21)?jdate_words(array('h'=>$num)):
			jdate_words(array('h'=>(int)($num/10))).' و '.jdate_words(array('h'=>$num%10));
		}
		else{$f=jdate_words(array('h'=>$num));}
		break;

		case 'mm':$key=array('فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند');$f=$key[$num-1];break;
		case 'mn':$key=array('فرو','ارد','خرد','تیر','مرد','شهر','مهر','آبا','آذر','دی','بهم','اسف');$f=$key[$num-1];break;
		case 'rh':$key=array('یکشنبه','دوشنبه','سه شنبه','چهارشنبه','پنجشنبه','جمعه','شنبه');$f=$key[$num];break;
		case 'RL':$key=array('یک','دو','سه','چهار','پنج','جمعه','شنبه');$f=$key[$num];break;
		case 'sh':$key=array('مار','اسب','گوسفند','میمون','مرغ','سگ','خوک','موش','گاو','پلنگ','خرگوش','نهنگ');$f=$key[$num%12];break;
		case 'mb':$key=array('حمل','ثور','جوزا','سرطان','اسد','سنبله','میزان','عقرب','قوس','جدی','دلو','حوت');$f=$key[$num-1];break;
		case 'h':$key=array('صفر','یک','دو','سه','چهار','پنج','شش','هفت','هشت','نه','ده','یازده','دوازده','سیزده','چهارده','پانزده','شانزده','هفده','هجده','نوزده','بیست');$f=$key[$num];break;
	}
}
return $f;
}

function tr_num($str,$mod='en',$mf='٫')
{
$num_a=array('0','1','2','3','4','5','6','7','8','9','.');
$key_a=array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹',$mf);
return($mod=='fa')?str_replace($num_a,$key_a,$str):str_replace($key_a,$num_a,$str);
}

?>
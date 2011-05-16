#!/usr/bin/perl
use Expect;
use POSIX;
use Audio::Wav;
use Cwd;


sub helptext() {
    print <<"EOF";
linphone.pl

Usage:  linphone.pl 79033039855 /var/www/sound.wav
  79033039855		phone number in international format
  /var/www/sound.wav	wav file to play
EOF
}

if (not defined $ARGV[0] or not defined $ARGV[1]) {
    helptext();
    exit 1;
}

call ($ARGV[0],$ARGV[1]);



sub call() {
my ($phoneNumber, $file) = @_;
# 	my $file = '/var/www/ERC/ocweb/application/tmp/4b420ac0b019237d24d9f2763ee95c9c.mp3.wav';
# 	my $phoneNumber = "79625552986";
# 	получаем длину файла
	my $wav = new Audio::Wav;
	my $read = $wav -> read( $file );
	my $soundLength =  ceil ( $read->length_seconds() );
# 	если длина больше единички 
	if( $soundLength > 1)
	{
	    my $dir = getcwd;
	    my $params = "-c $dir/linphonerc -l /dev/null -d 0";
          #  print "$params\n\n\n\n";
	    my $exp = Expect->spawn("linphonec $params");
	    my $timeout = 40;      
	  
	    $exp->expect($timeout,
	# 	  ожидаем регастрации в сипнете
		  [ 'Registration on sip:sipnet.ru successful.' => sub {
		      $exp->send("soundcard use files\n");
		      exp_continue; }
		  ],
	# переключаем на проигрывание файлов
	    [ 'Using wav files instead of soundcard.' => sub {
		$exp->send("call $phoneNumber\n");
	    exp_continue; }
	    ],
	# абонент взял трубку
	    [ 'Connected.' => sub {
	#Задержка перед началом воспроизведения	
	sleep(2);
	# начинаем проигрывание файла
		$exp->send("play $file\n");
	# время на проигрывание файла
		sleep($soundLength);
	# закрываемся
		$exp->send("terminate\n");
		sleep(1);
		$exp->send("quit\n");
		sleep(1);
		exit 0;
		exp_continue;
	    }
	    ],
	# сбросили трубку (занято)
	    [ 'User does not want to be disturbed.' => sub {
		$exp->send("quit\n");
	    }
	    ],
	# звонок завершен (трубка повешена)
	    [ 'Call terminated.' => sub {
		$exp->send("quit\n");
	    }
	    ],
	# таймаут на все провсе
	    [timeout => sub {
		$exp->send("quit\n");
	    }
	    ]
	    );
	    $exp->soft_close();
	}
}
exit 1;

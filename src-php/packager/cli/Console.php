<?php

namespace packager\cli;

use packager\Colors;
use php\io\Stream;
use php\lang\System;
use php\lib\arr;
use php\lib\str;
use php\util\Scanner;

use kosogroup\jphp\cli\AnsiConsole;
use php\lib\char;
use php\util\Regex;

use function PHPSTORM_META\argumentsSet;

class Console
{

    protected static $__RN = false;

    public static function isXTerm(): bool
    {
        static $xterm;

        if ($xterm === null) {
            $xterm = str::equalsIgnoreCase($_ENV['TERM'], 'xterm');
        }

        return $xterm;
    }


    public static function logTask($message, ...$args)
    {
        static::print("  " . $message, 10, ...$args);
    }

    public static function logValue($key, $value)
    {
        
        $width = AnsiConsole::getTerminalWidth();
        $keyWidth = str::length(static::clearText($key));
        $valueWidth = str::length(static::clearText($value));

        $message = '  ' . $key . ' ' . Colors::withColor(str::repeat('.', $width - $keyWidth - $valueWidth - 6), 'gray') . ' ' . $value . '  ';
        static::print($message);
        System::out()->write("\n");
        static::$__RN = false;
    }

    public static function logTaskResult($fail = false)
    {
        static::print(".. " . ($fail ? Colors::withColor('FAIL', 'red') : Colors::withColor('DONE', 'green')));
        System::out()->write("\n");
        static::$__RN = false;
    }

    protected static function clearText($message)
    {
        return (new Regex("\\e\[[0-9;]*m", 0, $message))->replace('');
    }

    public static function log($message, ...$args)
    {
        static::print($message, 0, ...$args);

        System::out()->write("\n");
        static::$__RN = false;
    }

    public static function printForXterm($message, ...$args)
    {
        if (Console::isXTerm()) {
            static::print($message, 0, ...$args);
        }
    }

    public static function print($message, $offset = 0, ...$args)
    {
        $stream = System::out();

        foreach ($args as $i => $arg) {
            $message = str::replace($message, "{{$i}}", Colors::withColor($arg, 'green'));
        }

        if ($offset > 0) {
            $width = AnsiConsole::getTerminalWidth() - $offset;
            $messageWidth = str::length(static::clearText($message));

            if ($messageWidth > $width) {
                $message = str::sub($message, 0, $width - 1) . ' ';
            } elseif ($messageWidth < $width) {
                $message = $message . ' ' . str::repeat('.', $width - $messageWidth - 1);
            }
        }

        $stream->write($message);
    }

    public static function debug($message, ...$args)
    {
        global $app;
        if ($app->isDebug()) {
            static::log(Colors::withColor('(debug)', 'silver') . " $message", ...$args);
        }
    }

    public static function warn($message, ...$args)
    {
        static::log((static::$__RN ? "  " : "\n  ") . Colors::withColor(' WARN ', 'yellow_bg') . " $message \n", ...$args);
        static::$__RN = true;
    }

    public static function error($message, ...$args)
    {
        static::log((static::$__RN ? "  " : "\n  ") . Colors::withColor(' FAIL ', 'red_bg') . " $message \n", ...$args);
        static::$__RN = true;
    }

    public static function info($message, ...$args)
    {
        static::log((static::$__RN ? "  " : "\n  ") . Colors::withColor(' INFO ', 'magenta_bg') . " $message \n", ...$args);
        static::$__RN = true;
    }

    public static function badged($message, $badge, $color, ...$args)
    {
        static::log((static::$__RN ? "  " : "\n  ") . Colors::withColor($badge, $color) . "$message\n", ...$args);
        static::$__RN = true;
    }

    public static function returnValue($key, $value)
    {
        static::print("\r" . char::of(27) . "[F");
        static::logValue($key, $value);
    }

    public static function readYesNo(string $message, bool $default = false): bool
    {
        $result = str::lower(static::read("$message (Y/n)", $default ? "yes" : "no"));

        if (arr::has(['yes', 'y'], $result)) return true;
        if (arr::has(['no', 'n'], $result)) return false;

        static::log(" -> please enter " . Colors::withColor('Y', 'green') . " (yes) or " . Colors::withColor('N', 'yellow') . " (no), try again ...");

        return static::readYesNo($message, $default);
    }

    public static function read(string $message, string $default = null): string
    {
        $stream = Stream::of('php://stdout', 'w');
        $stream->write($message . " ");

        if ($default) {
            $stream->write("(default: " . Colors::withColor($default, 'green') . ") ");
        }

        $stdin = new Scanner(Stream::of('php://stdin', 'r'));
        if ($stdin->hasNextLine()) {
            $line = $stdin->nextLine();
            if (!$line) {
                static::returnValue($message, $default);
                return $default;
            }

            static::returnValue($message, $line);
            return $line;
        }

        return null;
    }
}

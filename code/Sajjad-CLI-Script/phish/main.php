#!/usr/bin/env php
<?php
require 'vendor/autoload.php';

use PhpParser\Error;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Expr\FuncCall;

if (isset($argv[1])) {
    $GLOBALS["dir"] = $argv[1]; # '/Users/sajjad/Desktop/copyzip/mobiletd/logging.php';
} else {
    die("argv[1] is mandatory\n");
}
$GLOBALS["input"] = array();
function low($in)
{
    if ($in instanceof String_) {
        return strtolower($node->var->value);
    }
    return strtolower($in);
}

$code = file_get_contents($dir, true);

$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
try {
    $ast = $parser->parse($code);
} catch (Error $error) {
    // echo "Parse error: {$error->getMessage()}\n";
    return;
}

// echo dirname($dir);
// echo basename($dir);
$dumper = new NodeDumper;
//echo $dumper->dump($ast) . "\n";


$appender = new NodeTraverser();
$appender->addVisitor(new class extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Expression && $node->expr !== null) {

            if ($node->expr instanceof Node\Expr\Include_) {
                global $dir;
                $path = dirname($dir) . "/" . $node
                    ->expr
                    ->expr->value;
                // echo $path."\r\n";
                if (file_exists($path) && $node->expr->expr instanceof String_) {
                    $code = file_get_contents($path, true);
                    $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
                    try {
                        $ast = $parser->parse($code);
                        return $ast;
                    } catch (Error $error) {
                        // echo "Parse error: {$error->getMessage()}\n";
                        return;
                    }
                }
            }
        }
    }
});

foreach (range(1, 10) as $i) {
    $ast = $appender->traverse($ast);
}

$traverser = new NodeTraverser();
$traverser->addVisitor(new class extends NodeVisitorAbstract
{
    public function enterNode(Node $node)
    {
        if ($node instanceof ArrayDimFetch) {
            if (in_array(low($node
                ->var
                ->name), array(
                "_get",
                "_post",
                "_request",
                "_cookie"
            ))) {
                global $input;
                $node
                    ->dim->value = $node
                    ->var->name . "_" . $node
                    ->dim->value;
                $node
                    ->var->name = "_ENV";
                array_push($input, $node->dim->value);
                // echo '$_ENV["' . $node
                //     ->dim->value . "\"]\n";
            } else if (low($node
                ->var
                ->name) == "_server") {
                switch ($node
                    ->dim
                    ->value) {
                    case 'SERVER_NAME':
                        return new String_("concordia.ca");
                    case 'HTTP_CLIENT_IP':
                        return new String_("123.123.123.123");
                    case 'HTTP_X_FORWARDED_FOR':
                        return new String_("123.123.123.123");
                    case 'REMOTE_ADDR':
                        return new String_("123.123.123.123");

                    case 'HTTP_USER_AGENT':
                        return new String_("Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:47.0) Gecko/20100101 Firefox/47.0");
                }
            }
        } else if ($node instanceof FuncCall) {
            switch ($node->name) {
                case 'gethostname':
                    return new String_("123.123.123.123");
                case 'gethostbyaddr':
                    return new String_("123.123.123.123");
                case 'getenv':
                    switch ($node->args[0]->value->value) {
                        case 'REMOTE_ADDR':
                            return new String_("123.123.123.123");
                    }
            }
            if ($node->name == 'die') { //unnecessary
                return new String_("die");
            }
        } else if ($node instanceof Node\Expr\Exit_) { {
                return new String_("exit");
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Expression && $node->expr !== null) {
            if ($node->expr instanceof Node\Expr\ErrorSuppress) {
                $node->expr = $node->expr->expr;
            }
            $expr = $node->expr;
            while (!in_array($ex->name, array(
                "file_put_contents",
                "file",
                "fopen",
                "SQLite3"
            ))) {
                if ($expr instanceof FuncCall) {
                    $ex = $expr;
                }
                if ($expr->left->expr instanceof FuncCall) {
                    $ex = $expr->left->expr;
                }
                if ($expr->right->expr instanceof FuncCall) {
                    $ex = $expr->right->expr;
                }
                foreach ($expr as $value) {
                    if ($value instanceof Node\Expr\ErrorSuppress) {
                        $value = $value->expr;
                    }
                    if ($value instanceof FuncCall) {
                        $ex = $value;
                    }
                }
                if (in_array($ex->name, array(
                    "file_put_contents",
                    "file",
                    "fopen",
                    "SQLite3"
                )) or !$ex instanceof FuncCall) {
                    break;
                } else {
                    $flag = false;
                    foreach ($ex->args as $v) {
                        if ($v->value instanceof Node\Expr\ErrorSuppress) {
                            $v->value = $v->value->expr;
                        }

                        if ($v->value instanceof FuncCall) {
                            $flag = true;
                            $expr = $v->value;
                            break;
                        }
                    }
                    if ($flag != true) {
                        break;
                    }
                }
            }
            if (in_array($ex->name, array(
                "file_put_contents",
                "file",
                "fopen",
                "SQLite3"
            ))) {
                return [new Node\Stmt\Expression(new Node\Expr\FuncCall(new Node\Name("file_put_contents"), array(
                    new String_("F1ls.txt"),
                    $ex->args[0],
                    new Node\Scalar\LNumber(8)
                ))), new Node\Stmt\Expression(new Node\Expr\FuncCall(new Node\Name("file_put_contents"), array(
                    new String_("F1ls.txt"),
                    new String_("\r\n"),
                    new Node\Scalar\LNumber(8)
                ))), $node];
            }
        }
    }
});

$ast = $traverser->traverse($ast);

echo json_encode($input);
$prettyPrinter = new PrettyPrinter\Standard;
if (isset($argv[2])) {
    file_put_contents($argv[2], $prettyPrinter->prettyPrintFile($ast));
} else {
    echo $prettyPrinter->prettyPrintFile($ast);
}

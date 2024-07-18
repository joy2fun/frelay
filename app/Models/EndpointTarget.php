<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

class EndpointTarget extends Model
{
    use HasFactory;

    public function endpoint()
    {
        return $this->belongsTo(Endpoint::class, 'endpoint_id', 'id');
    }

    public function telescopeUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => sprintf("/%s/client-requests?tag=target:%s", config('telescope.path'), $this->id),
        );
    }

    public function buildHeaders()
    {
        $headers = $this->headers ?? '';
        $placeholders = self::parsePlaceHolders($headers);
        
        if ($placeholders) {
            $trans = $this->evaluatePlaceholdersAsTrans($placeholders);
            $headers = strtr($headers, $trans);
        }

        $headers = json_decode($headers, true);

        if (!$headers) {
            // pass through all headers except the "host"
            $headers = collect(request()->headers->all())->except("host")->toArray();
        }

        // append a special header for tagging telescope entity
        $headers['x-target-id'] = $this->id;

        return $headers;
    }

    public function buildBody(): array
    {
        $body = $this->body ?? '';
        $placeholders = self::parsePlaceHolders($body);

        if ($placeholders) {
            $trans = $this->evaluatePlaceholdersAsTrans($placeholders);
            $body = strtr($body, $trans);
        }

        $body = json_decode($body, true);

        return $body ?: request()->all();
    }

    public static function parsePlaceHolders(string $input): array
    {
        $all = [];
        preg_match_all("~{{(?<expr>.*?)}}~ix", $input, $matches);

        if ($matches['expr'] ?? null) {
            $expressions = array_unique($matches['expr']);
            foreach ($expressions as $expr) {
                if (strlen(trim($expr))) {
                    $all[] = $expr;
                }
            }
        }

        return array_unique($all);
    }

    public function evaluatePlaceholdersAsTrans(array $placeholders): array
    {
        $trans = [];
        foreach ($placeholders as $expr) {
            $trans[sprintf("{{%s}}", $expr)] = $this->evaluate($expr);
        }
        return $trans;
    }

    public function evaluate($expr)
    {
        try {
            return (new ExpressionLanguage)->evaluate($expr, [
                'req' => request(),
                'now' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function passedRule()
    {
        if (is_null($this->rule) || !strlen($this->rule)) {
            return true;
        }

        $result = $this->evaluate($this->rule);
        Log::warning(sprintf('rule [%s] evaluated', $this->rule), [$result]);

        return $result;
    }
}

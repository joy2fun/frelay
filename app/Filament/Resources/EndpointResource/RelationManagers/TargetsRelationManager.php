<?php

namespace App\Filament\Resources\EndpointResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\EndpointTarget;
use Filament\Tables\Actions\Action;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Filament\Resources\RelationManagers\RelationManager;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class TargetsRelationManager extends RelationManager
{
    protected static string $relationship = 'targets';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('uri')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Radio::make('method')->inline()->default('POST')->inlineLabel(false)
                    ->options(['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'])
                    ->columnSpan(1),
                Forms\Components\Textarea::make('rule')->rules([
                    fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail) {
                        try {
                            (new ExpressionLanguage())->lint($value, ['req', 'now']);
                        } catch (SyntaxError $e) {
                            $fail($e->getMessage());
                        }
                    }
                ]),
                Forms\Components\Textarea::make('headers')->rules([
                    $this->lintPlaceholders()
                ])->rows(5),
                Forms\Components\Textarea::make('body')->rules([
                    $this->lintPlaceholders()
                ])->rows(5),
            ]);
    }

    private function lintPlaceholders()
    {
        return fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail) {
            try {
                $lang = new ExpressionLanguage;
                $exprs = EndpointTarget::parsePlaceHolders($value);
                foreach ($exprs as $expr) {
                    $lang->lint($expr, ['req', 'now']);
                }
            } catch (SyntaxError $e) {
                $fail($e->getMessage());
            }
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('method')->disabledClick(),
                Tables\Columns\TextColumn::make('uri')->disabledClick(),
                Tables\Columns\TextColumn::make('rule')->disabledClick(),
                Tables\Columns\ToggleColumn::make('enabled'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Action::make('Logs')->url(fn (EndpointTarget $ep) => $ep->telescope_url)->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated(false);
    }
}

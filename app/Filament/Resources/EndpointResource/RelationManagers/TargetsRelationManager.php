<?php

namespace App\Filament\Resources\EndpointResource\RelationManagers;

use App\Models\EndpointTarget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Toggle::make('enabled')->inline(false)->default(1),
                Forms\Components\Radio::make('method')->inline()->default('POST')->inlineLabel(false)
                    ->options(['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'DELETE' => 'DELETE'])
                    ->columnSpan(1),
                Forms\Components\TextInput::make('uri')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('rule'),
                Forms\Components\Textarea::make('headers'),
                Forms\Components\Textarea::make('body'),
            ]);
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated(false);
    }
}

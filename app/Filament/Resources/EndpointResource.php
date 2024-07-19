<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EndpointResource\Pages;
use App\Filament\Resources\EndpointResource\RelationManagers\TargetsRelationManager;
use App\Models\Endpoint;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionsAction;
use Filament\Tables\Table;

class EndpointResource extends Resource
{
    protected static ?string $model = Endpoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make([
                    Forms\Components\TextInput::make('title')
                        ->required(),
                    Forms\Components\TextInput::make('slug')
                        ->prefix('/api/endpoint/')
                        ->unique()
                        ->required(),
                ])->columns(1)
                    ->extraAttributes(['class' => 'py-2 px-2'])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('url')->disabledClick(),
                Tables\Columns\TextColumn::make('targets_count')
                    ->counts('targets')
                    ->badge()
                    ->label('Targets'),
                Tables\Columns\ToggleColumn::make('enabled'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionsAction::make('Logs')->url(fn (Endpoint $ep) => $ep->telescope_url)->openUrlInNewTab(),
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

    public static function getRelations(): array
    {
        return [
            TargetsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEndpoints::route('/'),
            'create' => Pages\CreateEndpoint::route('/create'),
            'edit' => Pages\EditEndpoint::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $isCreate = $form->getOperation() === "create";
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->required()
                    ->unique('users')
                    ->email(),
                Forms\Components\TextInput::make('password')
                    ->type('password')
                    ->required(fn($get) => $isCreate)
                    ->confirmed()
                    ->minLength(5)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state)),
                Forms\Components\TextInput::make('password_confirmation')
                    ->type('password')
                    ->required()
                    ->required(fn($get) => $isCreate)
                    ->minLength(5)
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state)),
                Forms\Components\Select::make('roles')
                    ->relationship(name: 'roles', titleAttribute: 'name')

                    // ->saveRelationshipsUsing(function (User $record, $state) {
                    //     $record->roles()->syncWithPivotValues($state, [config('permission.column_names.team_foreign_key') => getPermissionsTeamId()]);
                    // })
                    // ->multiple()
                    ->preload()
                    ->required()
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge(true)
                    ->color(static function ($state): string {
                        if ($state === 'admin') {
                            return 'success';
                        }

                        return 'warning';
                    })
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()->date('Y-m-d'),

            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}

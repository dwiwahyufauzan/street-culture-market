<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Order Status & Metadata')
                    ->schema([
                        TextInput::make('order_number')
                            ->disabled()
                            ->required(),

                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),

                        Select::make('payment_status')
                            ->options([
                                'unpaid' => 'Unpaid',
                                'paid' => 'Paid',
                                'refunded' => 'Refunded',
                            ])
                            ->required(),

                        TextInput::make('payment_method')
                            ->placeholder('Midtrans / Manual Transfer'),
                    ])->columns(2),

                Section::make('Customer Snapshot')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Registered Customer Account')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        TextInput::make('customer_name')->required(),
                        TextInput::make('customer_email')->email()->required(),
                        TextInput::make('customer_phone')->tel(),
                    ])->columns(2),

                Section::make('Logistics & Shipping')
                    ->schema([
                        Textarea::make('shipping_address')->columnSpanFull(),
                        TextInput::make('shipping_city'),
                        TextInput::make('shipping_province'),
                        TextInput::make('shipping_postal'),
                        TextInput::make('shipping_method')->label('Courier'),
                        TextInput::make('shipping_cost')->numeric()->prefix('Rp')->default(0),
                    ])->columns(3),

                Section::make('Garments Purchased')
                    ->schema([
                        Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                TextInput::make('product_name')->required()->disabled(),
                                TextInput::make('size')->disabled(),
                                TextInput::make('quantity')->numeric()->required(),
                                TextInput::make('price')->numeric()->prefix('Rp')->required(),
                                TextInput::make('subtotal')->numeric()->prefix('Rp')->required(),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->addable(false)
                            ->deletable(false),
                    ]),

                Section::make('Financials & Notes')
                    ->schema([
                        TextInput::make('subtotal')->numeric()->prefix('Rp')->required(),
                        TextInput::make('discount_amount')->numeric()->prefix('Rp')->default(0),
                        TextInput::make('total')->numeric()->prefix('Rp')->required(),
                        Textarea::make('notes')->columnSpanFull(),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                TextColumn::make('customer_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('shipping_method')
                    ->label('Courier')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'unpaid' => 'Unpaid',
                        'paid' => 'Paid',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}

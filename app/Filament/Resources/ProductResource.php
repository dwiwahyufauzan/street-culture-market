<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Garment Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Product::class, 'slug', ignoreRecord: true),

                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('sku')
                            ->label('SKU')
                            ->default(fn () => 'SCM-'.strtoupper(Str::random(6)))
                            ->required(),

                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Pricing & Specifications')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        TextInput::make('sale_price')
                            ->numeric()
                            ->prefix('Rp')
                            ->helperText('Leave empty if not currently on sale drop'),

                        TextInput::make('weight')
                            ->label('Garment Weight')
                            ->numeric()
                            ->suffix('grams')
                            ->default(300),
                    ])->columns(3),

                Section::make('Product Variants (Sizes & Stock)')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship('variants')
                            ->schema([
                                Select::make('size')
                                    ->options([
                                        'XS' => 'XS',
                                        'S' => 'S',
                                        'M' => 'M',
                                        'L' => 'L',
                                        'XL' => 'XL',
                                        'XXL' => 'XXL',
                                        'One Size' => 'One Size',
                                    ])
                                    ->required(),

                                TextInput::make('color')
                                    ->placeholder('Washed Black / Vintage Gray'),

                                TextInput::make('stock')
                                    ->numeric()
                                    ->default(10)
                                    ->required(),

                                TextInput::make('sku')
                                    ->placeholder('Auto or Custom Variant SKU'),
                            ])
                            ->columns(4)
                            ->defaultItems(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Gallery Images')
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Photo')
                                    ->disk('public')
                                    ->directory('products')
                                    ->image()
                                    ->imageEditor()
                                    ->required(),

                                TextInput::make('alt_text')
                                    ->placeholder('Garment front angle description'),

                                Toggle::make('is_primary')
                                    ->label('Primary Cover Image')
                                    ->default(false),

                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),

                Section::make('Recommendations (Thesis Core: Upsell vs Cross-sell)')
                    ->schema([
                        Select::make('upsellProducts')
                            ->label('Up-selling Upgrades (Higher Tier)')
                            ->relationship('upsellProducts', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Select premium alternatives (e.g. heavier GSM, luxury hardware)'),

                        Select::make('crossSells')
                            ->label('Cross-selling Style Coordinates (Complete The Look)')
                            ->relationship('crossSells', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Select matching pieces (e.g. tees paired with cargo pants)'),
                    ])->columns(2),

                Section::make('Storefront Visibility & SEO')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active on Storefront')
                            ->default(true),

                        Toggle::make('is_featured')
                            ->label('Featured in Capsule Section')
                            ->default(false),

                        TextInput::make('meta_title')
                            ->placeholder('Defaults to Garment Name'),

                        Textarea::make('meta_description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('primaryImage.image_path')
                    ->label('Cover')
                    ->disk('public')
                    ->square(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $record) => $record->sku),

                TextColumn::make('category.name')
                    ->badge()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('sale_price')
                    ->money('IDR')
                    ->placeholder('-')
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}

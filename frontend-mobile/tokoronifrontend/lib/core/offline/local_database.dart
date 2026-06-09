import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

class LocalDatabase {
  LocalDatabase._();
  static final LocalDatabase instance = LocalDatabase._();

  static const _databaseName = 'tokoroni_offline.db';
  static const _databaseVersion = 2;

  static const tableProducts = 'offline_products';
  static const tableCategories = 'offline_categories';
  static const tableTransactionDrafts = 'offline_transaction_drafts';
  static const tableCart = 'offline_cart';
  static const tableCartItems = 'offline_cart_items';
  static const tableSyncQueue = 'sync_queue';
  static const tableAppSession = 'offline_app_session';
  static const tableSyncMeta = 'offline_sync_meta';
  static const tableEntityCache = 'offline_entity_cache';
  static const tableNotifications = 'offline_notifications';

  Database? _db;

  Future<Database> get db async {
    final cached = _db;
    if (cached != null) return cached;

    final basePath = await getDatabasesPath();
    final path = p.join(basePath, _databaseName);
    final database = await openDatabase(
      path,
      version: _databaseVersion,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
      onConfigure: (db) async {
        await db.execute('PRAGMA foreign_keys = ON');
      },
    );
    _db = database;
    return database;
  }

  Future<void> _onCreate(Database db, int version) async {
    await _createCoreTables(db);
  }

  Future<void> _onUpgrade(Database db, int oldVersion, int newVersion) async {
    await _createCoreTables(db);
  }

  Future<void> _createCoreTables(Database db) async {
    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableProducts(
        local_id TEXT PRIMARY KEY,
        server_id INTEGER,
        temp_id INTEGER,
        code TEXT NOT NULL,
        name TEXT NOT NULL,
        category_id INTEGER,
        category_name TEXT,
        description TEXT,
        stock INTEGER NOT NULL DEFAULT 0,
        min_stock INTEGER NOT NULL DEFAULT 10,
        unit TEXT NOT NULL DEFAULT 'pcs',
        barcode TEXT,
        weight REAL,
        dimensions TEXT,
        expiry_date TEXT,
        is_active INTEGER NOT NULL DEFAULT 1,
        price INTEGER NOT NULL DEFAULT 0,
        cost_price REAL,
        sync_status TEXT NOT NULL DEFAULT 'synced',
        local_revision INTEGER NOT NULL DEFAULT 1,
        updated_at TEXT NOT NULL,
        deleted_at TEXT,
        last_error TEXT
      )
    ''');

    await db.execute('''
      CREATE UNIQUE INDEX IF NOT EXISTS idx_products_server_id
      ON $tableProducts(server_id)
      WHERE server_id IS NOT NULL
    ''');

    await db.execute('''
      CREATE UNIQUE INDEX IF NOT EXISTS idx_products_temp_id
      ON $tableProducts(temp_id)
      WHERE temp_id IS NOT NULL
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_products_sync_status
      ON $tableProducts(sync_status)
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableCategories(
        local_id TEXT PRIMARY KEY,
        server_id INTEGER,
        temp_id INTEGER,
        name TEXT NOT NULL,
        slug TEXT NOT NULL,
        description TEXT,
        is_active INTEGER NOT NULL DEFAULT 1,
        products_count INTEGER NOT NULL DEFAULT 0,
        sync_status TEXT NOT NULL DEFAULT 'synced',
        local_revision INTEGER NOT NULL DEFAULT 1,
        updated_at TEXT NOT NULL,
        deleted_at TEXT,
        last_error TEXT
      )
    ''');

    await db.execute('''
      CREATE UNIQUE INDEX IF NOT EXISTS idx_categories_server_id
      ON $tableCategories(server_id)
      WHERE server_id IS NOT NULL
    ''');

    await db.execute('''
      CREATE UNIQUE INDEX IF NOT EXISTS idx_categories_temp_id
      ON $tableCategories(temp_id)
      WHERE temp_id IS NOT NULL
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_categories_sync_status
      ON $tableCategories(sync_status)
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableTransactionDrafts(
        local_id TEXT PRIMARY KEY,
        server_id INTEGER,
        temp_id INTEGER,
        invoice_number TEXT NOT NULL,
        customer_name TEXT,
        customer_phone TEXT,
        payment_method TEXT,
        payment_status TEXT,
        total_amount INTEGER NOT NULL DEFAULT 0,
        payload_json TEXT NOT NULL,
        sync_status TEXT NOT NULL DEFAULT 'pending_create',
        local_revision INTEGER NOT NULL DEFAULT 1,
        updated_at TEXT NOT NULL,
        deleted_at TEXT,
        last_error TEXT
      )
    ''');

    await db.execute('''
      CREATE UNIQUE INDEX IF NOT EXISTS idx_transaction_drafts_server_id
      ON $tableTransactionDrafts(server_id)
      WHERE server_id IS NOT NULL
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableCart(
        local_id TEXT PRIMARY KEY,
        user_id TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'active',
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableCartItems(
        local_id TEXT PRIMARY KEY,
        cart_local_id TEXT NOT NULL,
        product_local_id TEXT,
        product_server_id INTEGER,
        product_name TEXT NOT NULL,
        product_code TEXT NOT NULL,
        qty INTEGER NOT NULL,
        price INTEGER NOT NULL,
        updated_at TEXT NOT NULL,
        FOREIGN KEY(cart_local_id) REFERENCES $tableCart(local_id) ON DELETE CASCADE
      )
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_cart_items_cart_local_id
      ON $tableCartItems(cart_local_id)
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableSyncQueue(
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        entity_type TEXT NOT NULL,
        entity_local_id TEXT NOT NULL,
        operation TEXT NOT NULL,
        payload_json TEXT,
        retry_count INTEGER NOT NULL DEFAULT 0,
        next_retry_at TEXT,
        last_error TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_sync_queue_retry
      ON $tableSyncQueue(next_retry_at, retry_count, created_at)
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_sync_queue_entity
      ON $tableSyncQueue(entity_type, entity_local_id)
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableAppSession(
        key TEXT PRIMARY KEY,
        value TEXT,
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableSyncMeta(
        key TEXT PRIMARY KEY,
        value TEXT,
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableEntityCache(
        cache_key TEXT PRIMARY KEY,
        payload_json TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE IF NOT EXISTS $tableNotifications(
        local_id TEXT PRIMARY KEY,
        server_id TEXT,
        dedupe_key TEXT,
        source TEXT NOT NULL,
        title TEXT NOT NULL,
        message TEXT NOT NULL,
        type TEXT NOT NULL,
        priority TEXT NOT NULL DEFAULT 'normal',
        is_important INTEGER NOT NULL DEFAULT 0,
        is_read INTEGER NOT NULL DEFAULT 0,
        sync_status TEXT NOT NULL DEFAULT 'synced',
        payload_json TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_notifications_server_id
      ON $tableNotifications(server_id)
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_notifications_dedupe
      ON $tableNotifications(dedupe_key)
    ''');

    await db.execute('''
      CREATE INDEX IF NOT EXISTS idx_notifications_read
      ON $tableNotifications(is_read, created_at)
    ''');
  }
}

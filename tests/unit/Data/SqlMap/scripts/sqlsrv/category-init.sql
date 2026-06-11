DELETE FROM Categories;
DBCC CHECKIDENT('Categories', RESEED, 0);

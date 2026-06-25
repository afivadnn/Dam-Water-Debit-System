package com.konstruksi.debitair.data.local

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.Query
import androidx.room.Update

@Dao
interface PencatatanDao {
    @Insert
    suspend fun insert(pencatatan: PencatatanEntity)

    @Query("SELECT * FROM tabel_pencatatan_lokal WHERE isSynced = 0")
    suspend fun getUnsyncedData(): List<PencatatanEntity>

    @Query("UPDATE tabel_pencatatan_lokal SET isSynced = 1 WHERE id = :id")
    suspend fun markAsSynced(id: Int)

    @Query("DELETE FROM tabel_pencatatan_lokal WHERE isSynced = 1")
    suspend fun clearSyncedData()

    @Query("SELECT * FROM tabel_pencatatan_lokal WHERE tanggal = :tanggal AND idPetugas = :idPetugas AND idBendungan = :idBendungan")
    suspend fun getDataByDate(tanggal: String, idPetugas: Int, idBendungan: Int): List<PencatatanEntity>
}
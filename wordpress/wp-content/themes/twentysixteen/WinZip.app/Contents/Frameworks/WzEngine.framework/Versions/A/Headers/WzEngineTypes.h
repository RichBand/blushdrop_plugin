//
//  WzEngineTypes.h
//  WzEngine
//
//  Copyright 2010 Winzip. All rights reserved.
//


typedef long long int WzFileIndex;

// compression methods for WzFileDetails
// these should be actual algorithm identifiers
typedef enum {
	WCM_NOVALUE,            // the no-value value
	WCM_UNKNOWN,            // the unknown type value
	WCM_STORE,              // only store the file - no compression
	WCM_BZIP2,              // use BZip2 compression
	WCM_DEFLATE,            // standard (32-bit) deflate algorithm
	WCM_DEFLATE64,          // 64-bit deflate algorithm
	WCM_JZIP,               // JPEG compression
	WCM_LZMA,               // LZMA compression
	WCM_PPMD,               // PPMd compression
	WCM_WAVPACK,            // WavPack compression
	WCM_SHRINK,             // old shrink algorithm (extract only)
	WCM_REDUCE1,            // old reduce algorithm with compression factor 1 (extract only)
	WCM_REDUCE2,            // old reduce algorithm with compression factor 2 (extract only)
	WCM_REDUCE3,            // old reduce algorithm with compression factor 3 (extract only)
	WCM_REDUCE4,            // old reduce algorithm with compression factor 4 (extract only)
	WCM_IMPLODE,            // old implode algorithm (extract only)
	WCM_TOKENIZED,          // reserved (in APPNOTE.TXT) for a tokenizing compression method
	WCM_PKWARE_DCL_IMPLODE, // PKWARE Data Compression Library Imploding (old IBM TERSE)
	WCM_RAR_FASTEST,        // RAR fastest compression (method 0x30)
	WCM_RAR_FAST,           // RAR fast compression (method 0x32)
	WCM_RAR_NORMAL,         // RAR normal compression (method 0x33)
	WCM_RAR_GOOD,           // RAR good compression (method 0x34)
	WCM_RAR_BEST,           // RAR best compression (method 0x35)
	WCM_LZHUFF0,            // LHA/LZH no compression
	WCM_LZHUFF1,            // LHA/LZH 2^12 dictionary
	WCM_LZHUFF2,            // LHA/LZH 2^13 dictionary
	WCM_LZHUFF3,            // LHA/LZH 2^13 dictionary
	WCM_LZHUFF4,            // LHA/LZH 2^12 dictionary
	WCM_LZHUFF5,            // LHA/LZH 2^13 dictionary
	WCM_LZHUFF6,            // LHA/LZH 2^15 dictionary
	WCM_LZHUFF7,            // LHA/LZH 2^16 dictionary
	WCM_LARC,               // LHA/LZH 2^11 dictionary
	WCM_LARC4,              // LHA/LZH no compression
	WCM_LARC5,              // LHA/LZH 2^12 dictionary
	WCM_LZHDIRS,            // LHA/LZH directory
	WCM_COPY,               // copy file - no compression
	WCM_LZMA2               // LZMA2 compression
} WzCompressionMethod;

// responses from AttributePrompt and DotDotPrompt callbacks
typedef enum     // wynacr
{
    YNAC_YES,
    YNAC_NO,
    YNAC_ALL,
    YNAC_CANCEL
} WzYesNoAllCancelResponse;

// responses from ReplacePrompt callback
typedef enum     // wynanrcr
{
	YNANRC_YES,
	YNANRC_NO,
	YNANRC_ALL,
	YNANRC_NONE,
	YNANRC_RENAME,
	YNANRC_CANCEL
} WzYesNoAllNoneRenameCancelResponse;

typedef enum   // wcmo
{
	CM_STANDARD,    // standard compression for archive format
	CM_BEST_METHOD, // use best method available
	CM_STORE,       // only store the file - no compression
	CM_BZIP2,       // use BZip2 compression
	CM_DEFLATE,     // standard (32-bit) deflate algorithm
	CM_DEFLATE64,   // 64-bit deflate algorithm
	CM_JZIP,        // JPEG compression
	CM_LZMA,        // LZMA compression
	CM_PPMD,        // PPMd compression
	CM_WAVPACK      // WavPack compression
} WzCompressionMethodOption;

typedef enum    // wemo
{
	EM_BASIC,       // default encryption scheme for archive format (if any)
	EM_ADVANCED,    // advanced encryption scheme for archive format (if any)
	EM_NONE,        // no encryption
	EM_AES128,      // AES 128-bit keys
	EM_AES192,      // AES 192-bit keys
	EM_AES256       // AES 256-bit keys
} WzEncryptionMethodOption;

// encryption methods for WzFileDetails
//  these should be actual algorithm identifiers
typedef enum     // wem
{
	WEM_NOVALUE,    // the no-value value
	WEM_UNKNOWN,    // the unknown type value
	WEM_NONE,       // no encryption
	WEM_AES128,     // AES 128-bit keys
	WEM_AES192,     // AES 192-bit keys
	WEM_AES256,     // AES 256-bit keys
	WEM_CLASSIC,    // Zip classic encryption
	WEM_PKWARE,     // PKWARE Strong Encryption
	WEM_RAR         // RAR Encrypted
} WzEncryptionMethod;

// parameter options for IncorrectPassword callback
typedef enum // wppo
{
	PP_DEFAULT,         // default "Incorrect Password" dialog
	PP_RECRYPT_FIRST,   // actions\encrypt first prompt
	PP_RECRYPT_RETRY    // actions\encrypt retry prompt
} WzPasswordPromptOption;

// responses from  callback
typedef enum // wbpr
{
	IP_SKIP,        // skip extracting this file
	IP_NEWPSWD,     // new password returned
	IP_ABORT        // cancel the rest of the extract operation
} WzIncorrectPasswordResponse;

// error codes for archives
typedef enum {
	WZAERR_NO_ERROR                      = 0,     // no error
	
    WZAERR_UNSUPPORTED_OPTION            = 1,     // attempt to set an option not supported by the archive
    WZAERR_UNSUPPORTED_OPERATION         = 2,     // attempt to perform an operation not supported by the archive
    WZAERR_FILE_DETAIL_NOT_SPECIFIED     = 3,     // attempt to access a file detail that isn't specified
    WZAERR_OUT_OF_MEMORY                 = 4,     // insufficient memory

    WZAERR_UNKNOWN_EXCEPTION             = 5,     // caught an unexpected exception (reminder: do we really want to do this?)
    WZAERR_ARCHIVE_NOT_LOADED            = 6,     // the archive must be loaded before operations can be performed
    WZAERR_UNABLE_TO_OPEN_FOR_OUTPUT     = 7,     // the archive file cannot be opened for output
    WZAERR_USER_ABORT                    = 8,     // user abort requested via callback
    WZAERR_UNABLE_TO_ERASE_FILE          = 9,    // could not erase a file

    WZAERR_UNABLE_TO_RENAME_FILE         = 10,    // could not rename a file
    WZAERR_UNABLE_TO_SEEK_FILE           = 11,    // seek operation failed
    WZAERR_UNABLE_TO_READ_FILE           = 12,    // read operation failed
    WZAERR_UNABLE_TO_WRITE_FILE          = 13,    // write operation failed
    WZAERR_UNABLE_TO_REMOVE_DIRECTORY    = 14,    // remove directory operation failed

    WZAERR_UNABLE_TO_OPEN_FOR_INPUT      = 15,    // the archive file cannot be opened for input
    WZAERR_UNABLE_TO_FIND_PART           = 16,    // cannot find an archive part or diskette
    WZAERR_UNEXPECTED_END_OF_FILE        = 17,    // attempt to read past end of file
    WZAERR_NOTHING_TO_LOAD               = 18,    // archive doesn't exist or has no files in it
    WZAERR_NOTHING_TO_EXTRACT            = 19,    // no files where found to extract

    WZAERR_NOTHING_TO_TEST               = 20,    // no files where found to test
    WZAERR_EXTRACT_PATH_TOO_LONG         = 21,    // path for extracted file is too long, can't extract
    WZAERR_EXTRACT_NAME_TOO_LONG         = 22,    // filename for extracted file is too long, can't extract
    WZAERR_DISK_FULL                     = 23,    // not enough room on target disk
    WZAERR_BAD_FORMAT                    = 24,    // something is wrong with the archive format

    WZAERR_BAD_OPTION                    = 25,    // something is wrong with an option for the operation
    WZAERR_UNSUPPORTED_ENCRYPTION_METHOD = 26,    // the requested encryption method is not supported
    WZAERR_FILE_NOT_FOUND                = 27,    // file is not in archive
    WZAERR_FILE_NOT_LOADED               = 28,    // file is not loaded
    WZAERR_INVALID_REQUEST_READONLY      = 29,    // invalid request for read-only archive

    WZAERR_UNABLE_TO_CREATE_FILE         = 30,    // file create failed
    WZAERR_UNABLE_TO_FIND_FILEID         = 31,    // the fileid was not found in the loaded archive
    WZAERR_UNABLE_TO_CREATE_DIRECTORY    = 32,    // unable to create directory
    WZAERR_BAD_PASSWORD                  = 33,    // incorrect or missing password for archive
    WZAERR_FIPS_MODULE                   = 34,    // FIPS 140-2 validated cryptographic module not found

    WZAERR_FIPS_ALGORITHM                = 35,    // FIPS 140-2 security function violation
    WZAERR_FIPS_POLICY                   = 36,    // FIPS 140-2 compliance not enabled
    WZAERR_UNKNOWN_ERROR                 = 37,    // unknown error
    WZAERR_NO_CALLBACKS                  = 38,    // temp error, should be removed after whole engine upgraded.

	WZERR_7Z_LIBRARY_LOAD				 = 5001,  // unable to load library
	WZERR_7Z_NEED_PASSWORD				 = 5002,  // encrypted; no password specified
	WZAERR_7Z_EXTRACT_FAILURE			 = 5003,  // extract failure
    
	WZERR_RAR_ENC_BAD_PASSWORD           = 1001,  // rar archive encrypted; invalid/missing password specified
    WZERR_RAR_END_ARCHIVE                = 1002,  // end of archive reached (eof)
    WZERR_RAR_NO_MEMORY                  = 1003,  // out-of-memory condition
    WZERR_RAR_BAD_HEADER                 = 1004,  // malformed header detected
    WZERR_RAR_BAD_DATA                   = 1005,  // malformed data detected (or bad password)
    WZERR_RAR_CRC_ERROR                  = 1006,  // crc error detected
    WZERR_RAR_BAD_ARCHIVE                = 1007,  // rar archive has invalid/damaged content
    WZERR_RAR_UNKNOWN_ARCHIVE_FORMAT     = 1008,  // archive format unknown
    WZERR_RAR_UNKNOWN_FORMAT             = 1009,  // archive header format unknown (known header encryption)
    WZERR_RAR_EOPEN                      = 1010,  // archive open error
    WZERR_RAR_ECREATE                    = 1011,  // output creation error
    WZERR_RAR_ECLOSE                     = 1012,  // archive close error
    WZERR_RAR_EREAD                      = 1013,  // archive read error
    WZERR_RAR_EWRITE                     = 1014,  // output write error
    WZAERR_RAR_MISSING_PASSWORD          = 1015,  // file not decrypted, no password supplied
    WZERR_RAR_UNKNOWN                    = 1016,  // internal error
    WZERR_RAR_NO_EXTRACT_DEST_PATH       = 1017,
    
	WZAERR_LHA_ERROR_BAD_ARCHIVE                 = 2001,
	WZAERR_LHA_ERROR_HEADER_INVALID_LHARC_HEADER = 2020,
    WZAERR_LHA_ERROR_LIB_CANT_UPDATE_ARCHIVE     = 2033,
	WZAERR_LHA_ERROR_LIB_NOTHING_DONE            = 2045,
    WZAERR_LHA_CANT_MAKE_DIRECTORY               = 2047
} WzErrors;

// constants indicating error level
typedef enum    // welvl
{
    WZERR_LVL_NONE        = 0,          // leave space for new insertions between levels
    WZERR_LVL_WARNING     = 10,
    WZERR_LVL_CORRECTABLE = 20,
    WZERR_LVL_SEVERE      = 30,
    WZERR_LVL_FATAL       = 40
} WzErrorLevel;

typedef enum {      // wdcr
	DC_OK,          // OK - new disk inserted
	DC_OK_WIPEDISK, // OK - but erase any files on disk
	//      DC_OK_FORMAT,   // OK - but format the disk before using (not currently supported)
	DC_CANCEL       // CANCEL the rest of the zip operation
} WzDiskChangeResponse;

// parameter options for MsgBox callback
typedef enum             // wmbo
{
    WMB_OK                    = 0x000,
    WMB_YESNO                 = 0x004,
    WMB_YESNOCANCEL           = 0x003,
    WMB_ICONSTOP              = 0x010,
    WMB_ICONWARNING           = 0x030,
    WMB_ABORTRETRYIGNORE      = 0x002,
    WMB_DEFBUTTON2            = 0x100,
    WMB_OKCANCEL              = 0x001
} WzMsgBoxOption;

// responses from MsgBox callback
typedef enum            // wmbr
{
    WMBR_ABORT                 = 3,
    WMBR_CANCEL                = 2,
    WMBR_CONTINUE              = 11,
    WMBR_IGNORE                = 5,
    WMBR_NO                    = 7,
    WMBR_OK                    = 1,
    WMBR_RETRY                 = 4,
    WMBR_TRYAGAIN              = 10,
    WMBR_YES                   = 6
} WzMsgBoxResponse;

// parameter options for MsgBox callback
typedef enum    // wswo
{
    SW_STATUS_ONLY,     // status text only for display on status line
    SW_VIEWLAST_ONLY,   // status text for later display -- not on status line
    SW_METER2_ONLY      // status text only for meter two
} WzStatusWindowOption;

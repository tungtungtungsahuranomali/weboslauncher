-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 31 Mar 2026 pada 10.50
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `take_off`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `display_name`, `password_hash`, `role`, `created_at`) VALUES
(1, 'rizal', 'rizal', '$2y$10$EhDVx1DdZwoL3N.3dzQFfOmFBCVM.Txe1TUMtUMoaOZRKkVh8A98K', 'superadmin', '2025-10-27 10:44:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_permissions`
--

CREATE TABLE `admin_permissions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `page_key` varchar(50) NOT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin_permissions`
--

INSERT INTO `admin_permissions` (`id`, `admin_id`, `page_key`, `allowed`) VALUES
(17, 1, 'dashboard', 1),
(18, 1, 'devices', 1),
(19, 1, 'checkin', 1),
(20, 1, 'send_notification', 1),
(21, 1, 'facilities', 1),
(22, 1, 'amenities', 1),
(23, 1, 'information', 1),
(24, 1, 'dining', 1),
(25, 1, 'dining_orders', 1),
(26, 1, 'amenity_requests', 1),
(27, 1, 'app_control', 1),
(28, 1, 'running_text', 1),
(29, 1, 'update', 1),
(30, 1, 'flashscreen', 1),
(31, 1, 'server_config', 1),
(32, 1, 'users', 1),
(65, 1, 'iptv', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `amenity_requests`
--

CREATE TABLE `amenity_requests` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL COMMENT 'JSON array of requested items',
  `status` enum('Pending','Delivered','Cancelled') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `amenity_requests`
--

INSERT INTO `amenity_requests` (`id`, `room_number`, `guest_name`, `items`, `status`, `requested_at`) VALUES
(1, '102', 'Guest', '[{\"id\":14,\"name\":\"Sajadah\",\"description\":\"Alat sholat (1 set)\",\"icon_path\":\"http://192.168.1.169/AHFix/uploads/amenities/amenity_1762854461_1137.jpg\",\"qty\":1}]', 'Pending', '2026-03-15 11:03:36'),
(2, '101', 'Taji', '[{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1},{\"id\":14,\"name\":\"Sajadah\",\"description\":\"Alat sholat (1 set)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854461_1137.jpg\",\"qty\":1},{\"id\":17,\"name\":\"Sajadah\",\"description\":\"Alat Shalat 1 set\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/am_1764212513.jpg\",\"qty\":1}]', 'Pending', '2026-03-16 08:00:25'),
(3, '101', 'Taji', '[{\"id\":13,\"name\":\"Perlengkapan Mandi\",\"description\":\"Sabun, Shampoo, Sikat Gigi\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854476_7748.jpg\",\"qty\":1}]', 'Pending', '2026-03-16 08:00:26'),
(4, '101', 'Taji', '[{\"id\":12,\"name\":\"Bantal Tambahan\",\"description\":\"Bantal tidur ekstra (1 buah)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854489_6848.jpg\",\"qty\":1}]', 'Pending', '2026-03-16 08:00:28'),
(5, '101', 'Taji', '[{\"id\":15,\"name\":\"Air Mineral\",\"description\":\"Air mineral botol (2 botol)\",\"icon_path\":\"http://10.10.10.129/AHFix/uploads/amenities/amenity_1762854450_2040.jpg\",\"qty\":1}]', 'Pending', '2026-03-16 08:00:29');

-- --------------------------------------------------------

--
-- Struktur dari tabel `app_settings`
--

CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL,
  `package` varchar(100) NOT NULL,
  `status` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `app_settings`
--

INSERT INTO `app_settings` (`id`, `package`, `status`, `updated_at`) VALUES
(1, 'com.google.android.youtube.tv', 1, '2025-11-05 08:32:40'),
(2, 'com.netflix.ninja', 1, '2025-11-05 08:32:40'),
(3, 'in.startv.hotstar.dplus.tv', 1, '2025-11-05 08:32:40'),
(4, 'com.vidio.android.tv', 1, '2025-11-05 08:32:40'),
(5, 'com.spotify.tv.android', 1, '2025-11-05 08:32:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `channels`
--

CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `lcn` int(11) NOT NULL DEFAULT 0 COMMENT 'Logical Channel Number (urutan)',
  `title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT 'Umum',
  `stream_url` text NOT NULL,
  `logo_url` text DEFAULT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `channels`
--

INSERT INTO `channels` (`id`, `lcn`, `title`, `category`, `stream_url`, `logo_url`, `status`, `created_at`) VALUES
(3, 3, 'TRANSTV', 'Channel TV', 'https://video.detik.com/transtv/smil:transtv.smil/chunklist_w2114898498_b744100_sleng.m3u8', 'https://www.transtv.co.id/themes/v25.7/src/assets/logo/transtv-white.png', 'enabled', '2026-03-05 05:39:19'),
(4, 4, 'TRANS 7', 'Channel TV', 'https://video.detik.com/trans7/smil:trans7.smil/chunklist_w964486842_b744100_sleng.m3u8', 'https://www.transtv.co.id/themes/v25.7/src/assets/logo/transtv-white.png', 'enabled', '2026-03-05 05:39:19'),
(8, 8, 'MDTV', 'Channel TV', 'https://d3dlxh2qgfbmej.cloudfront.net/4b0d3ec5db06491983e7dcf493ad431c/index_2.m3u8?Policy=eyJTdGF0ZW1lbnQiOlt7IlJlc291cmNlIjoiaHR0cHM6Ly9kM2RseGgycWdmYm1lai5jbG91ZGZyb250Lm5ldC80YjBkM2VjNWRiMDY0OTE5ODNlN2RjZjQ5M2FkNDMxYy8qIiwiQ29uZGl0aW9uIjp7IkRhdGVMZXNzVGhhbiI6eyJBV1M6RXBvY2hUaW1lIjoxNzczNzY3ODQ0fX19XX0_&Signature=bzJrn2XOlj-%7E53UAy8MCb9FJj96EWjfZQIP8AZqkVIxLgjENShnk1X2J5a2crFSblVmF7LjYYTo%7En1C5YtZ4vvyy89hzxMuO9mG%7E7ZekrapptdOMO0hMu-dBWXVjpswln%7EqrG1woS5SrUxT6aLxMe1Pc9lDgc2FgGcJcHUIVk6LrTj0dr21HtPOXlAGpDHk75hDlvQHmEg%7E9uOHPJ-kTvYz6hIq5e3At4RCYvUbMiA1lbcEsaUgx-eaeHwEWlVsbVytn%7ET4jJkrI1YHYZYMPMfFowkocTYc7ts6SIWfTbpepKES4xTB7o%7Efv9uscmZrv2iEJwAMCTehl-pREaSlbXg__&Key-Pair-Id=K421JPZA23CPQ', 'https://thumbor.prod.vidiocdn.com/vkORCwK34mMDw2PbRGLcVLkux10=/filters:quality(70)/vidio-web-prod-livestreaming/uploads/livestreaming/image/875/mdtv-ff5756.jpg', 'enabled', '2026-03-05 05:39:19'),
(25, 25, 'NBA', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/436.m3u8', 'https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JKQvyxSBi_9A4CJxTYwEdAHwiZummrErsqmlWRw-A5GP.png', 'enabled', '2026-03-05 05:39:19'),
(29, 29, 'ESPN 1', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1674.m3u8', 'https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JCzHbGZkymHrQoDxAajCK5kw1L0at3yITHUd1Nzfju2t.png', 'enabled', '2026-03-05 05:39:19'),
(61, 61, 'Soccer Channel', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/31853.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG_9e68Rb4PUYlM7Ob1Ek2xgPzv6Qz0v4_id4_XJYM7-iVfn5QKaEFhNvXsobN1DDb1drcntnDauf9OyvtJRMhpMp1cHB_Opu8Ovys4fewUGK-quhlNAhIAkuCbLVNBhzg.png', 'enabled', '2026-03-05 05:39:19'),
(86, 86, 'TVRI', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1335.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9RnhxMR7jmm8PKxZ-r_RFSFRSWeqJo4kWnz44ELWtiDnHS78.png', 'enabled', '2026-03-05 05:39:19'),
(87, 87, 'SCTV', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1322.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGigRZG-YjkkFaSEwKFkXscYQHBYKDx-KXUdwl86CuN61urXGOthgTlJ5rhY6GKWaOm2ffoRNZJXE0ZB1Jx5jrU.png', 'enabled', '2026-03-05 05:39:19'),
(91, 91, 'TV ONE', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1345.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvPIdO7JUOvSIiiql7nxnaF0l3Hv43CO1tCc1DxK9Rnhxidx12F2y1zESgddR2oeQZ_4TGU7h2RO_rZIA_wSjtsFgkg9sQiYN5eRPP3zaZ0DRTAHMdV5WMHU12ILb9g684w.png', 'enabled', '2026-03-05 05:39:19'),
(94, 94, 'METROTV', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1328.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJAM8J_hkScWYN9JYVAL03KFp-K022xty5ORam2X0ZYiIXR0CubKJIFD6SsSfzmo364cSpX2lLrsMA6KVDYvxnuZWAQl5Pvo2XZFzXcw3iF82cE34Ct2YBEMiPm8jTxqlQ.png', 'enabled', '2026-03-05 05:39:19'),
(96, 96, 'KOMPASTV', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1331.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvClnLSRcZSCAJ_yyi46pjdsA4xccqyFB6gqv-MpHs9hY3LucTlPv913qLL2jCpyAxchkrTFoFg5Eut9FkP9Zb0g4wobw1nLTm-9atfS7bmKyXAVoKDAdG3Wbaqbtr0TxkA.png', 'enabled', '2026-03-05 05:39:19'),
(98, 98, 'JTV', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/33501.m3u8', 'https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JGskTN3IXNGos_y1zb3pUP2Bqkz4jH8gRV68SsxnTUih.png', 'enabled', '2026-03-05 05:39:19'),
(99, 99, 'CNN Indonesia', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1496.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvF8cUK5Jxtwj1NJ_7LBWZBmpUCBVTq6i6fYYwbyDLckgfoawZa4nKpuiQtrWH_Ds2SkyOrC-n4-S4xkzrNyIy3lbwk76uDdgeG9xhKDqGC1uU5ygyE8tfJbLll95A7-yQA.png', 'enabled', '2026-03-05 05:39:19'),
(105, 105, 'HBO', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1364.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3El6TXeIRDoSxM9ayVU1Ajeh0BRnkv8ivcl6r59cq9N2nQ.png', 'enabled', '2026-03-05 05:39:19'),
(111, 111, 'HBO Family', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1370.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3ElJMRXEVFUBruJa7hOabcaLVA0u_0O5rpMstXQkE70P7HpQRApUJoBPKcs4y1VUaO4L6_-vPyJLPBJoJ7N9RA2oA.png', 'enabled', '2026-03-05 05:39:19'),
(123, 123, 'Thrill', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1386.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOQxg-Y5LD1E7ybrkrYtdLg4gkLVRAKUTXm4bvr_VNxK1yOWvy58soSkSYFQl_jI2IA3iBYgVehSMtA8JoKV5OBCQ79GbzsH0WVPSD15TBZk6LjolGsp6nlSXuH-tH3YQg.png', 'enabled', '2026-03-05 05:39:19'),
(126, 126, 'Galaxy', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1390.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBEzlR0s4WXXyoV-k_dKmgACLmYnxL2sUaO_-RjhnQlLa6AnzLPtqXmmZ9ixEg7KvfTtKALSpr91mwuJzmLLZIdw1MM5JuUdZNMWn_yeT-ZFlGqAAtZrrQmgebin8oHYmA.png', 'enabled', '2026-03-05 05:39:19'),
(129, 129, 'HITS', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1397.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwbSS-HAO-xicYK_sU6BsRO5DmYuv3Rbu7fsp52m3EltWZl6k7_R_waNyNEc5lIBxOULB6TJlLRRvfe4XGkowY.png', 'enabled', '2026-03-05 05:39:19'),
(131, 131, 'TVN', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1400.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu2dUnXIxMFlB8AbBJpTAS94Gb18M4h3RydamQltV4nrTcG8fWNvmJ0eTsPfvbC0e0fGX0rF-V1ZHvc1AAp4CmNw.png', 'enabled', '2026-03-05 05:39:19'),
(135, 135, 'AXN', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1402.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGV1nsyv3Qm7N-OAVaJb2-f-z-SNlEgOZRoXZkkw0qQELZwuzG8m8RvnbbKZcTzCPuxnBxqVA4xOyttIIIV_IRo.png', 'enabled', '2026-03-05 05:39:19'),
(137, 137, 'IMC', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1403.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGNX-ieeiu-ztsaEwUYdjfEI1AK5lVosDgtfxlYABK1m_iNOd96aCd3TNgS2tumTB_D3CaHvlpcTeCeAL3o5fKU.png', 'enabled', '2026-03-05 05:39:19'),
(151, 151, 'KBSWorld', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1413.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu7MkDHoaVlp7b1OdpU0t70VwKT5BoiSFB-UYz_lAYSZietne3YJ_xUn7dRkxCThy8KlVp0xyLM7ESA0U5Cgig2Q.png', 'enabled', '2026-03-05 05:39:19'),
(155, 155, 'AFN', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1423.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOOsTdARvfNJXvHneAui8VT_7MQHg2EXQBq9fhRqbd7cCSYjJddBQBaMocJ_pRRZphiioLJXPH1BM5C-wqfPkV0lI3yBa2xYuZao_wz1LQsxQEXkTlB_TNlMBqYi6lU3CQ.png', 'enabled', '2026-03-05 05:39:19'),
(156, 156, 'TLC', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1424.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvOOsTdARvfNJXvHneAui8VT_7MQHg2EXQBq9fhRqbd7cd6lswWlbEYFSpIbudyQ9VRWWxYzzX0InrrgUI4AjH3V7ei1AZH0F7a2bZenIfSmTs4BgXYVjicbrTF8zhactzg.png', 'enabled', '2026-03-05 05:39:19'),
(159, 159, 'BBC News', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/708.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvAPf96nLVrEt4TOZIjZXa4uw8egpVwi4-J7u2PAD-ROyLyGA05tIqvsOljwCa-RntW2bNfImgyFXK_TMYQWRFlDKJ5Xl7dAGrdWHE5DKkeaZTaYZy3HxaTQLmxB2aSeiIA.png', 'enabled', '2026-03-05 05:39:19'),
(164, 164, 'Discovery Asia', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1438.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBK_Rk-tg6-RvoRn8vKgFZ_q9szAuGR-Ib_288Me9Q_qeIt1vc8e96xSydhkPdgpF8dLwofOhkkoYcokIY9mBXTFV57tGXhixaTqw_IJa-7ftDmLvQo2AXqlOiLmNALVcA.png', 'enabled', '2026-03-05 05:39:19'),
(165, 165, 'Discovery Channel', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1439.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvBK_Rk-tg6-RvoRn8vKgFZ_q9szAuGR-Ib_288Me9Q_qGOLBlggwXHwmLKt19YH0HLC_9gm9uEsr-lG9u3wHv7IfAWpN43hvDEJlnN1nCXl14gxYFy1SfWoVx4zAE9DBWg.png', 'enabled', '2026-03-05 05:39:19'),
(171, 171, 'Dreamworks', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1457.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvGwGkxhhOov0w5t8qcu-f62CmNUbSd1k34jHUqtzgCMiuz-LvIBZ0qe3NxhH5k8sXNunr8KaIC6BnnoBtdj3i8eud3jUzgs9y9juN1jcL3NWf9f225RqG3gJjF-x7-A3KQ.png', 'enabled', '2026-03-05 05:39:19'),
(172, 172, 'Nick Junior', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/601.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvO9_jZCw8CoP6gBmAjcZzwrMBS27_-F7eoezuNGKswsmwbpYZYRQiWGun33lgZ931YuFqeWoj73zLd8KtuKlDkAOK9i2wPpxxA-kPfSNYPAI2qA5dQxyfZjt2aFSYOFfjg.png', 'enabled', '2026-03-05 05:39:19'),
(181, 181, 'Channel News Asia', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1491.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBFbmizf4UtxpG1h3lwOLOQwnKxApsXfkMCg2cGoffic8s.png', 'enabled', '2026-03-05 05:39:19'),
(182, 182, 'DW English', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1492.m3u8', 'https://ogietv.biz.id:443/images/QCqX5p5x3Y181Q8jwb82JFD38V--sV3R73RYLjtbsCOCUKwzROuycDtnHzZIAqwW.png', 'enabled', '2026-03-05 05:39:19'),
(183, 183, 'CNBC Indonesia', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1497.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBFvkhy70SugTUHga_mFS8_LP_EWl2yeP-nKYOjYaEn41_1S5iWeYEaNdvgNQCF55her_9lb9L6AEy39Qc1-z7lzQ.png', 'enabled', '2026-03-05 05:39:19'),
(184, 184, 'CNN International', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/1498.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvG5N4h063eZc_EQFEzXSo5JkKtk63QQd29VfzXgexRBF9G1DbpLt1UjwpjIfAfsDkM_IldO4xUkYvL2ehwV9xQH5sADSawlrZWSnFvxsVgwQkD5rmAjGbNepOUY84kw8QQ.png', 'enabled', '2026-03-05 05:39:19'),
(213, 213, 'NHK World Japan', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/12848.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnuAS2sxdiW3LUIfzj-fsACV6A47ds7qA30zect1_qNuYYyU2sl_o8wTo80rRzBHrtDFWMkQi9ZwmABgf1VL_XWYA.png', 'enabled', '2026-03-05 05:39:19'),
(230, 230, 'Kids Tv', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/31900.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvKXSeS0bTp9NyDHxNmdfbsVOk9sLFF9HlwgchPhpCzo4tfDQJEc1fiENBBqv2ybeLFCye48jaWuLo0fXPPKfYV7KEqQNs_aMr94tID455nOAlswwzQnt1gH08FaHnqvTQQ.png', 'enabled', '2026-03-05 05:39:19'),
(232, 232, 'Cinemachi', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/63176.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvCwYMNr8HDJoefw3K5vYPlIR3OfEUt7vIXKFd6lMLNmRURygofgAi5OtRlY9p8lGnLEMV46-t2HYVJ4uaTMRShhCLp5FhZXQVmdOkn2YfLzvqnMxxyDPGBaJXULxbeBnrQ.png', 'enabled', '2026-03-05 05:39:19'),
(235, 235, 'Cinemachi Kids', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/63179.m3u8', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvICuwbx-ax6PRBT4esrRb4pC1eYje8wygtPXWkApaFOxlwIR8NfM0HQDcJaLucEjspYjf5LIWwhMgI2EI1j32Kde57QFN6uG1VxRkP-dIh7XBPgjFly63GvjG9CT0Yk8kA.png', 'enabled', '2026-03-05 05:39:19'),
(260, 260, 'Cctv 4', 'Channel TV', 'https://ogietv.biz.id:443/Livetv/234/82983.m3u8', 'https://ogietv.biz.id:443/images/yaxZJ07eVLvcPbZCcPO7I4iawnf9xiQzmvMZv-uNJds.png', 'enabled', '2026-03-05 05:39:19'),
(262, 39, 'KBS_World', 'Channel TV', 'http://172.31.15.1:80/kbs_world', 'https://ogietv.biz.id:443/images/41XrJW_rFnTScKiHhnyTkQuOes23-0Jm5fBjqlIAbVeaqSAKcBQctvWpp0tMy8NYjfn8b3q3uW29JYUn3kfsvJuVz0Vh2rNO2lypfG57MVtVTw6E-EVvk14pgiFVLwnu7MkDHoaVlp7b1OdpU0t70VwKT5BoiSFB-UYz_lAYSZietne3YJ_xUn7dRkxCThy8KlVp0xyLM7ESA0U5Cgig2Q.png', 'enabled', '2026-03-05 07:27:18'),
(263, 40, 'Discovery', 'Channel TV', 'http://172.31.15.2:80/Discovery', '', 'enabled', '2026-03-05 08:26:13'),
(264, 41, 'nhk_world', 'Channel TV', 'http://172.31.15.3:80/nhk_world', '', 'enabled', '2026-03-05 08:26:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `device_units`
--

CREATE TABLE `device_units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(255) NOT NULL,
  `launcher_script` text NOT NULL,
  `restore_script` text NOT NULL,
  `clear_script` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `device_units`
--

INSERT INTO `device_units` (`id`, `unit_name`, `launcher_script`, `restore_script`, `clear_script`, `created_at`) VALUES
(1, 'TCL', 'shell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME\r\nshell pm disable-user --user 0 com.google.android.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell pm disable-user --user 0 com.google.android.leanbacklauncher\r\nshell pm uninstall -k --user 0 com.google.android.tv.launcherx\r\nshell pm uninstall -k --user 0 com.google.android.apps.tv.launcherx\r\nshell pm disable-user --user 0 com.google.android.tv.setupwraith\r\nshell pm disable-user --user 0 com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity', 'shell pm enable com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tvlauncher\r\nshell pm enable com.google.android.tv.launcherx\r\nshell pm enable com.google.android.leanbacklauncher\r\nshell cmd package install-existing --user 0 com.google.android.tv.launcherx\r\nshell cmd package install-existing --user 0 com.google.android.apps.tv.launcherx\r\nshell pm enable com.google.android.tv.setupwraith\r\nshell pm enable com.google.android.tungsten.setupwraith\r\nshell cmd package set-home-activity com.google.android.apps.tv.launcherx/.MainActivity\r\nshell am start -a android.intent.action.MAIN -c android.intent.category.HOME', 'com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv', '2026-03-09 10:00:36'),
(3, 'STB ZTE ZXV10 B66F', 'shell am start -n com.takeoff.launcher/.MainActivity', 'shell am start -n com.google.android.tvlauncher/.MainActivity', 'com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv', '2026-03-09 20:36:16'),
(4, 'STB ZTE ZXV10 B860H', 'shell pm disable-user --user 0 com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.takeoff.launcher/.MainActivity\r\nshell am start -n com.takeoff.launcher/.MainActivity', 'shell pm enable com.google.android.tvlauncher\r\nshell cmd package set-home-activity com.google.android.tvlauncher/.MainActivity\r\nshell am start -n com.google.android.tvlauncher/.MainActivity', 'com.google.android.youtube.tv\r\ncom.netflix.ninja\r\ncom.spotify.tv.android\r\ncom.vidio.android.tv', '2026-03-10 10:53:01');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dining_menu`
--

CREATE TABLE `dining_menu` (
  `id` int(11) NOT NULL,
  `id_kat_dining` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL DEFAULT 0,
  `image_url` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `dining_menu`
--

INSERT INTO `dining_menu` (`id`, `id_kat_dining`, `name`, `name_en`, `description`, `price`, `image_url`, `status`) VALUES
(1, 3, 'Nasi Goreng Spesial', 'Special Fried Rice', NULL, 25000, 'uploads/dining/menu_1772371852_1849.jpg', 'active'),
(2, 3, 'Mie Goreng Seafood', 'Seafood Fried Noodles', NULL, 28000, 'uploads/dining/menu_1772371845_2555.jpg', 'active'),
(3, 3, 'Sate Ayam Madura', 'Madura Chicken Satay', NULL, 32000, 'uploads/dining/menu_1772371838_9425.jpg', 'active'),
(4, 1, 'Soto Ayam Lamongan', 'Lamongan Chicken Soup', NULL, 27000, 'uploads/dining/menu_1772371831_4362.jpg', 'active'),
(5, 2, 'Ayam Penyet Sambal Ijo', 'Smashed Chicken (Green Chili)', NULL, 30000, 'uploads/dining/menu_1772371824_8410.jpg', 'active'),
(6, 2, 'Capcay Kuah', 'Capcay Soup', NULL, 26000, 'uploads/dining/menu_1772371817_8370.jpg', 'active'),
(7, 2, 'Teh Manis Dingin', 'Iced Sweet Tea', NULL, 8000, 'uploads/dining/menu_1772371810_5717.jpg', 'active'),
(8, 1, 'Kopi Hitam Tubruk', 'Black Coffee', NULL, 10000, 'uploads/dining/menu_1772371803_2895.jpg', 'active'),
(9, 1, 'Jus Alpukat', 'Avocado Juice', NULL, 15000, 'uploads/dining/menu_1772371795_3214.jpg', 'active'),
(10, 1, 'Pisang Goreng Keju', 'Fried Banana w/ Cheese', NULL, 18000, 'uploads/dining/menu_1772371788_2092.jpg', 'active'),
(11, 1, 'Nasi Goreng Terasi', 'Shrimp Paste Fried Rice', NULL, 35000, 'uploads/dining/menu_1772371780_9870.jpg', 'active');

-- --------------------------------------------------------

--
-- Struktur dari tabel `general_info`
--

CREATE TABLE `general_info` (
  `id` int(11) NOT NULL,
  `id_kat_general_info` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `general_info`
--

INSERT INTO `general_info` (`id`, `id_kat_general_info`, `title`, `title_en`, `description`, `description_en`, `icon_path`, `show_description`, `is_active`) VALUES
(1, 2, 'tyrty', 'trytry', 'trytry', 'trytytry', 'uploads/general_info/gen_info_1773355096.jpg', 1, 1),
(2, 1, '65776', '657567', '567567', '657567', 'uploads/general_info/gen_info_1773355118.jpg', 1, 1),
(3, 2, 'ewrer', 'ewrewr', 'werewr', 'ewrewr', 'uploads/general_info/gen_info_1773563267.jpg', 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `global_settings`
--

CREATE TABLE `global_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `global_settings`
--

INSERT INTO `global_settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'launcher_enabled', '1'),
(4, 'default_volume', '10'),
(10, 'system_version', 'v251227-034321'),
(13, 'splash_enabled', '1'),
(16, 'launcher_bg', ''),
(17, 'launcher_home_bg', 'uploads/homebg/launcher_home_bg.jpg?v=1766986021'),
(26, 'loading_logo_url', 'uploads/logo/loading_logo.png?v=1762812096'),
(29, 'custom_greeting_title', 'Welcome'),
(30, 'custom_welcome_greeting', 'Selamat datang di Hotel Harris.\r\nKami sangat senang menyambut Anda sebagai tamu istimewa kami.\r\nNikmati kenyamanan kamar, layanan ramah, serta suasana modern dan kreatif yang telah kami siapkan untuk membuat masa inap Anda lebih berkesan.\r\nJika Anda membutuhkan bantuan kapan saja, tim kami selalu siap melayani dengan sepenuh hati.\r\nSelamat beristirahat & enjoy your stay!\r\n\r\n— Branch Manager, Hotel Harris'),
(31, 'custom_greeting_image', 'uploads/greeting/greeting_img.jpg?v=1769756199');

-- --------------------------------------------------------

--
-- Struktur dari tabel `guest_checkin`
--

CREATE TABLE `guest_checkin` (
  `id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `guest_name` varchar(100) NOT NULL,
  `checkin_time` datetime NOT NULL DEFAULT current_timestamp(),
  `checkout_time` datetime DEFAULT NULL,
  `status` enum('checked_in','checked_out') NOT NULL DEFAULT 'checked_in'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `guest_checkin`
--

INSERT INTO `guest_checkin` (`id`, `room_number`, `guest_name`, `checkin_time`, `checkout_time`, `status`) VALUES
(1, '101', 'JOKO P', '2026-03-10 03:00:45', '2026-03-10 04:05:14', 'checked_out'),
(2, '101', 'JOKO P', '2026-03-10 04:55:57', '2026-03-10 15:34:10', 'checked_out'),
(3, '101', 'Rizal', '2026-03-10 15:50:10', '2026-03-10 16:05:37', 'checked_out'),
(4, '101', 'Rizal', '2026-03-10 16:05:59', '2026-03-10 16:38:04', 'checked_out'),
(5, '101', 'Rizal', '2026-03-10 16:38:17', '2026-03-10 22:33:26', 'checked_out'),
(6, '102', 'Rizal', '2026-03-11 01:09:27', '2026-03-13 01:22:16', 'checked_out'),
(7, '102', 'Rizal', '2026-03-13 01:39:05', '2026-03-13 01:39:12', 'checked_out'),
(8, '101', 'Taji', '2026-03-16 14:54:18', NULL, 'checked_in');

-- --------------------------------------------------------

--
-- Struktur dari tabel `hotel_facilities`
--

CREATE TABLE `hotel_facilities` (
  `id` int(11) NOT NULL,
  `id_kat_facilities` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `show_description` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `hotel_facilities`
--

INSERT INTO `hotel_facilities` (`id`, `id_kat_facilities`, `name`, `name_en`, `icon_path`, `description`, `description_en`, `is_active`, `show_description`) VALUES
(1, 1, 'Classic', NULL, 'uploads/facilities/facility_1762374139_7591.jpg', '', NULL, 1, 0),
(2, 3, 'Hotel Service', NULL, 'uploads/facilities/facility_1762374173_2294.jpg', '', NULL, 1, 0),
(3, 1, 'Breakfast', NULL, 'uploads/facilities/facility_1762374195_8430.jpg', '', NULL, 1, 0),
(4, 2, 'Sales', NULL, 'uploads/facilities/facility_1762374266_8271.jpg', '', NULL, 1, 0),
(5, 2, 'Bedroom', NULL, 'uploads/facilities/facility_1762374289_5827.jpg', '', NULL, 1, 0),
(9, 2, 'random 123', NULL, 'uploads/facilities/facility_1762672461_6616.jpeg', 'test 123', NULL, 1, 0),
(10, 1, 'komplit', NULL, 'uploads/facilities/facility_1763880039_8678.jpg', 'ttt', NULL, 1, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `hotel_info`
--

CREATE TABLE `hotel_info` (
  `id` int(11) NOT NULL,
  `id_kat_info` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `title_en` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `show_description` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `hotel_info`
--

INSERT INTO `hotel_info` (`id`, `id_kat_info`, `title`, `title_en`, `description`, `description_en`, `icon_path`, `sort_order`, `created_at`, `show_description`) VALUES
(1, 1, 'Hotel Kami', NULL, '', NULL, 'uploads/info/info_1762383411_1981.jpg', 0, '2025-11-05 22:56:51', 0),
(2, 1, 'Check in', NULL, '', NULL, 'uploads/info/info_1762383434_1467.jpg', 0, '2025-11-05 22:57:14', 0),
(3, 2, 'Analitik', NULL, '', NULL, 'uploads/info/info_1762383448_2523.jpg', 0, '2025-11-05 22:57:28', 0),
(4, 2, 'Selamat Datang', NULL, '', NULL, 'uploads/info/info_1762383478_7320.jpg', 0, '2025-11-05 22:57:58', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `hotel_orders`
--

CREATE TABLE `hotel_orders` (
  `id` int(11) NOT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `items` text DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Delivered','Cancelled') DEFAULT 'Pending',
  `ordered_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kat_dining`
--

CREATE TABLE `kat_dining` (
  `id_kat_dining` int(11) NOT NULL,
  `nm_kat_dining` varchar(255) NOT NULL,
  `foto_kat_dining` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kat_dining`
--

INSERT INTO `kat_dining` (`id_kat_dining`, `nm_kat_dining`, `foto_kat_dining`) VALUES
(1, 'Indonesian Food', 'uploads/kat_dining/kat_dining_1773566226_9678.jpg'),
(2, 'Chinese Food', 'uploads/kat_dining/kat_dining_1773566254_5472.jpg'),
(3, 'Arabic Food', 'uploads/kat_dining/kat_dining_1773566307_8076.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kat_facilities`
--

CREATE TABLE `kat_facilities` (
  `id_kat_facilities` int(11) NOT NULL,
  `nm_kat_facilities` varchar(255) NOT NULL,
  `foto_kat_facilities` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kat_facilities`
--

INSERT INTO `kat_facilities` (`id_kat_facilities`, `nm_kat_facilities`, `foto_kat_facilities`) VALUES
(1, 'Kategori 1', 'uploads/kat_facilities/kat_1773351945_4666.jpg'),
(2, 'Kategori 2', 'uploads/kat_facilities/kat_1773351958_7609.jpg'),
(3, 'Kategori 3', 'uploads/kat_facilities/kat_1773351971_5910.jpg'),
(4, 'Kategori 4', 'uploads/kat_facilities/kat_1773351985_3802.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kat_general_info`
--

CREATE TABLE `kat_general_info` (
  `id_kat_general_info` int(11) NOT NULL,
  `nm_kat_general_info` varchar(255) NOT NULL,
  `foto_kat_general_info` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kat_general_info`
--

INSERT INTO `kat_general_info` (`id_kat_general_info`, `nm_kat_general_info`, `foto_kat_general_info`) VALUES
(1, 'Tentang kami', 'uploads/kat_general_info/kat_gen_info_1773355019_6260.jpg'),
(2, 'Fasilitas', 'uploads/kat_general_info/kat_gen_info_1773355069_1144.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kat_info`
--

CREATE TABLE `kat_info` (
  `id_kat_info` int(11) NOT NULL,
  `nm_kat_info` varchar(255) NOT NULL,
  `foto_kat_info` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kat_info`
--

INSERT INTO `kat_info` (`id_kat_info`, `nm_kat_info`, `foto_kat_info`) VALUES
(1, 'Apa Itu Hotel', 'uploads/kat_info/kat_info_1773548334_5928.png'),
(2, 'Sejarah', 'uploads/kat_info/kat_info_1773548354_1009.png'),
(3, 'Hotel', 'uploads/kat_info/kat_info_1773548432_6802.jpg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kat_promotion`
--

CREATE TABLE `kat_promotion` (
  `id_kat_promotion` int(11) NOT NULL,
  `nm_kat_promotion` varchar(255) NOT NULL,
  `foto_kat_promotion` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kat_promotion`
--

INSERT INTO `kat_promotion` (`id_kat_promotion`, `nm_kat_promotion`, `foto_kat_promotion`) VALUES
(1, 'promo 1', 'uploads/kat_promotion/kat_promo_1773566709_1406.jpg'),
(2, 'promo 2', 'uploads/kat_promotion/kat_promo_1773566718_6852.jpg'),
(3, 'promo 3', 'uploads/kat_promotion/kat_promo_1773566741_1101.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `managed_devices`
--

CREATE TABLE `managed_devices` (
  `id` int(11) NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `device_name` varchar(100) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `device_ip` varchar(45) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `pending_clear` tinyint(1) NOT NULL DEFAULT 0,
  `pending_start_launcher` tinyint(1) NOT NULL DEFAULT 0,
  `registered_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `managed_devices`
--

INSERT INTO `managed_devices` (`id`, `device_id`, `device_name`, `room_number`, `device_ip`, `unit_id`, `pending_clear`, `pending_start_launcher`, `registered_at`, `is_active`, `last_seen`) VALUES
(15, 'TV-VH72RS', 'Joko TV', '101', '192.168.1.62', 4, 0, 1, '2026-03-09 19:50:07', 1, '2026-03-20 21:58:19');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `rooms` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `playlists`
--

CREATE TABLE `playlists` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Nama provider',
  `url` text NOT NULL COMMENT 'URL file M3U',
  `default_category` varchar(100) DEFAULT 'Playlist',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `popup_notifications`
--

CREATE TABLE `popup_notifications` (
  `id` bigint(20) NOT NULL,
  `device_id` varchar(128) NOT NULL,
  `room_number` varchar(32) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `status` enum('pending','delivered','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `sound_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotion`
--

CREATE TABLE `promotion` (
  `id` int(11) NOT NULL,
  `id_kat_promotion` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `name_en` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `show_description` tinyint(1) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promotion`
--

INSERT INTO `promotion` (`id`, `id_kat_promotion`, `name`, `name_en`, `description`, `description_en`, `icon_path`, `show_description`, `is_active`) VALUES
(1, 1, 'adsadsd', 'asdsad', 'sadsadsa', 'sadsad', 'uploads/promotion/promo_1773566821.jpg', 1, 1),
(2, 1, 'sAS', 'ds', 'dsf', 'sdf', 'uploads/promotion/promo_1773566840.jpg', 1, 1),
(3, 2, 'dfsf', 'h', 'ghgh', 'gg', 'uploads/promotion/promo_1773566854.jpg', 1, 1),
(4, 3, 'ghh', 'ghh', 'ghgh', 'ghgh', 'uploads/promotion/promo_1773566869.jpg', 1, 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `room_amenities`
--

CREATE TABLE `room_amenities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data untuk tabel `room_amenities`
--

INSERT INTO `room_amenities` (`id`, `name`, `name_en`, `description`, `description_en`, `icon_path`, `category`, `created_at`) VALUES
(11, 'Handuk Tambahan', NULL, 'Handuk mandi ekstra (1 buah)', NULL, 'uploads/amenities/amenity_1762854506_5026.jpg', 'general', '2025-11-11 09:16:28'),
(12, 'Bantal Tambahan', NULL, 'Bantal tidur ekstra (1 buah)', NULL, 'uploads/amenities/amenity_1762854489_6848.jpg', 'general', '2025-11-11 09:16:28'),
(13, 'Perlengkapan Mandi', NULL, 'Sabun, Shampoo, Sikat Gigi', NULL, 'uploads/amenities/amenity_1762854476_7748.jpg', 'general', '2025-11-11 09:16:28'),
(14, 'Sajadah', NULL, 'Alat sholat (1 set)', NULL, 'uploads/amenities/amenity_1762854461_1137.jpg', 'general', '2025-11-11 09:16:28'),
(15, 'Air Mineral', NULL, 'Air mineral botol (2 botol)', NULL, 'uploads/amenities/amenity_1762854450_2040.jpg', 'general', '2025-11-11 09:16:28'),
(16, 'Teko Kopi', NULL, 'Kopi, teh, susu', NULL, 'uploads/amenities/amenity_1762855317_8455.jpg', 'general', '2025-11-11 10:01:57'),
(17, 'Sajadah', 'Prayer Place', 'Alat Shalat 1 set', 'Prayer Set', 'uploads/amenities/am_1764212513.jpg', 'general', '2025-11-27 03:01:53');

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_apps`
--

CREATE TABLE `system_apps` (
  `id` int(11) NOT NULL,
  `app_key` varchar(50) NOT NULL,
  `app_name` varchar(100) NOT NULL,
  `app_name_en` varchar(100) DEFAULT NULL,
  `icon_path` varchar(255) NOT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `android_package` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `system_apps`
--

INSERT INTO `system_apps` (`id`, `app_key`, `app_name`, `app_name_en`, `icon_path`, `is_visible`, `sort_order`, `android_package`) VALUES
(1, 'information', 'Information', 'Information', 'img/information.png', 1, 1, NULL),
(2, 'dining', 'Dining Room', 'Dining Room', 'img/diningroom.png', 1, 2, NULL),
(3, 'amenities', 'Amenities', 'Amenities', 'img/amenities.png', 1, 8, NULL),
(4, 'facilities', 'Facilities', 'Facilities', 'img/facilities.png', 1, 7, NULL),
(6, 'youtube', 'YouTube', NULL, 'img/youtube.png', 1, 9, 'com.google.android.youtube.tv'),
(7, 'netflix', 'Netflix', NULL, 'img/netflix.png', 1, 4, 'com.netflix.ninja'),
(8, 'spotify', 'Spotify', NULL, 'img/spotify.png', 1, 10, 'com.spotify.tv.android'),
(10, 'vidio', 'Vidio', NULL, 'img/vidio.png', 1, 11, 'com.vidio.android.tv'),
(23, 'clear_data_guest', 'Clear Data Guest', NULL, 'uploads/icons/icon_1769246765.png', 1, 6, 'clear.data'),
(25, 'tv_local', 'TV Channel', NULL, 'uploads/icons/icon_1772552379.png', 1, 5, 'com.mmaplay.iptv'),
(29, 'promotion', 'promotion', 'promotion', 'uploads/icons/icon_1773353782.png', 1, 3, NULL),
(30, 'general_info', 'General Information', NULL, 'uploads/icons/icon_1773563755.png', 1, 0, 'internal.general_info');

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_marquee`
--

CREATE TABLE `system_marquee` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `system_marquee`
--

INSERT INTO `system_marquee` (`id`, `content`, `is_active`, `last_updated`) VALUES
(1, '', 1, '2026-02-27 15:10:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('amenities_request_card_enabled', '1', '2026-03-16 15:00:04'),
('dining_cart_enabled', '1', '2026-03-16 14:59:20'),
('scheduled_clear_enabled', '1', '2026-02-28 02:34:20'),
('scheduled_clear_time', '02:15', '2026-02-28 02:34:20'),
('wa_fonnte_token', '3xpJ3q4kvKAehog6k14V', '2026-02-28 02:32:34'),
('wa_gateway_enabled', '1', '2026-02-28 02:32:34');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `idx_username_unique` (`username`);

--
-- Indeks untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_admin_page` (`admin_id`,`page_key`);

--
-- Indeks untuk tabel `amenity_requests`
--
ALTER TABLE `amenity_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lcn` (`lcn`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `device_units`
--
ALTER TABLE `device_units`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dining_menu`
--
ALTER TABLE `dining_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `general_info`
--
ALTER TABLE `general_info`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `global_settings`
--
ALTER TABLE `global_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `guest_checkin`
--
ALTER TABLE `guest_checkin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `room_number` (`room_number`,`status`);

--
-- Indeks untuk tabel `hotel_facilities`
--
ALTER TABLE `hotel_facilities`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `hotel_info`
--
ALTER TABLE `hotel_info`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `hotel_orders`
--
ALTER TABLE `hotel_orders`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kat_dining`
--
ALTER TABLE `kat_dining`
  ADD PRIMARY KEY (`id_kat_dining`);

--
-- Indeks untuk tabel `kat_facilities`
--
ALTER TABLE `kat_facilities`
  ADD PRIMARY KEY (`id_kat_facilities`);

--
-- Indeks untuk tabel `kat_general_info`
--
ALTER TABLE `kat_general_info`
  ADD PRIMARY KEY (`id_kat_general_info`);

--
-- Indeks untuk tabel `kat_info`
--
ALTER TABLE `kat_info`
  ADD PRIMARY KEY (`id_kat_info`);

--
-- Indeks untuk tabel `kat_promotion`
--
ALTER TABLE `kat_promotion`
  ADD PRIMARY KEY (`id_kat_promotion`);

--
-- Indeks untuk tabel `managed_devices`
--
ALTER TABLE `managed_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `playlists`
--
ALTER TABLE `playlists`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `popup_notifications`
--
ALTER TABLE `popup_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_status_created` (`device_id`,`status`,`created_at`);

--
-- Indeks untuk tabel `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `room_amenities`
--
ALTER TABLE `room_amenities`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `system_apps`
--
ALTER TABLE `system_apps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_key` (`app_key`);

--
-- Indeks untuk tabel `system_marquee`
--
ALTER TABLE `system_marquee`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT untuk tabel `amenity_requests`
--
ALTER TABLE `amenity_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `app_settings`
--
ALTER TABLE `app_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=265;

--
-- AUTO_INCREMENT untuk tabel `device_units`
--
ALTER TABLE `device_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `dining_menu`
--
ALTER TABLE `dining_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `general_info`
--
ALTER TABLE `general_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `global_settings`
--
ALTER TABLE `global_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT untuk tabel `guest_checkin`
--
ALTER TABLE `guest_checkin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `hotel_facilities`
--
ALTER TABLE `hotel_facilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `hotel_info`
--
ALTER TABLE `hotel_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `hotel_orders`
--
ALTER TABLE `hotel_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kat_dining`
--
ALTER TABLE `kat_dining`
  MODIFY `id_kat_dining` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kat_facilities`
--
ALTER TABLE `kat_facilities`
  MODIFY `id_kat_facilities` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `kat_general_info`
--
ALTER TABLE `kat_general_info`
  MODIFY `id_kat_general_info` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `kat_info`
--
ALTER TABLE `kat_info`
  MODIFY `id_kat_info` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `kat_promotion`
--
ALTER TABLE `kat_promotion`
  MODIFY `id_kat_promotion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `managed_devices`
--
ALTER TABLE `managed_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `playlists`
--
ALTER TABLE `playlists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `popup_notifications`
--
ALTER TABLE `popup_notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `promotion`
--
ALTER TABLE `promotion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `room_amenities`
--
ALTER TABLE `room_amenities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT untuk tabel `system_apps`
--
ALTER TABLE `system_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT untuk tabel `system_marquee`
--
ALTER TABLE `system_marquee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `admin_permissions`
--
ALTER TABLE `admin_permissions`
  ADD CONSTRAINT `fk_admin_permissions_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

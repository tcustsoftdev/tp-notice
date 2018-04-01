
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[Notices](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[Content] [nvarchar](max) NULL,
	[Staff] [bit] NOT NULL DEFAULT ('0'),
	[Teacher] [bit] NOT NULL DEFAULT ('0'),
	[Student] [bit] NOT NULL DEFAULT ('0'),
	[Units] [nvarchar](max) NULL,
	[Classes] [nvarchar](max) NULL,
	[Levels] [nvarchar](255) NULL,
	[Reviewed] [bit] NOT NULL DEFAULT ('0'),
	[CreatedBy] [nvarchar](255) NULL,
	[UpdatedBy] [nvarchar](255) NULL,
	[ReviewedBy] [nvarchar](255) NULL,
	[CreatedAt] [datetime] NULL,
	[UpdatedAt] [datetime] NULL,
	[ReviewedAt] [datetime] NULL,
	[PS] [nvarchar](max) NULL,
PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO


SET QUOTED_IDENTIFIER ON
GO

CREATE TABLE [dbo].[NoticeAttachment](
	[Id] [int] IDENTITY(1,1) NOT NULL,
	[Notice_Id] [int] NOT NULL,
	[Title] [nvarchar](255) NOT NULL,
	[Name] [nvarchar](255) NOT NULL,
	[Type] [nvarchar](255) NOT NULL,
	[FileData] [nvarchar](max) NOT NULL,
	[CreatedBy] [nvarchar](255) NULL,
	[UpdatedBy] [nvarchar](255) NULL,
	[CreatedAt] [datetime] NULL,
	[UpdatedAt] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[Id] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]

GO

ALTER TABLE [dbo].[NoticeAttachment]  WITH CHECK ADD  CONSTRAINT [noticeattachment_notice_id_foreign] FOREIGN KEY([Notice_Id])
REFERENCES [dbo].[Notices] ([Id])
ON DELETE CASCADE
GO

ALTER TABLE [dbo].[NoticeAttachment] CHECK CONSTRAINT [noticeattachment_notice_id_foreign]
GO

